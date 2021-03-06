"use strict";

exports.clear = clear;
exports.refresh = refresh;
exports.addPanel = addPanel;

//############################################################




var $ = require("dom");
var WS = require("tfw.web-service");
var PM = require("tfw.binding.property-manager");
var Panel = require("soin.view.panel");
var Dialog = require("soin.dialog");
var Splash = require("soin.splash");
var Logout = require("soin.view.panel.logout");
var SvcOrga = require("soin.svc-orga");
var Structures = require("soin.view.panel.structures");
var SvcDashboard = require("soin.svc-dashboard");
var PatientsList = require("soin.view.panel.patients-list");
var StatsOccurences = require("soin.view.panel.stats-occurences");

var g_dashboard = {
  options: {},
  // `[{ def:..., view:<this is a PANEL> }]`
  panels: []
};
var g_viewLogout;
var g_organizations;


function clear() {
  var panels = document.querySelectorAll("div.soin-view-panel");
  for( var i=0; i<panels.length; i++ ) {
    $.detach( panels[i] );
  }
}


function refresh() {
  return new Promise(function (resolve, reject) {
    clear();
    SvcDashboard.get().then(function( dashboard ) {
      console.info("[soin.dashboard] dashboard=", dashboard);
      g_dashboard = {
        options: dashboard.options,
        panels: [],
        // Temporary array  used to load  all panels one by  one.
        // @see loadAllPanels().
        defs: dashboard.panels
      };
      return SvcOrga.list();
    }).then(function( organizations ) {
      g_organizations = organizations;
      addPanelLogout( organizations );
      loadAllPanels();
      resolve();
    });
  });
}

function loadAllPanels() {
  if( !Array.isArray( g_dashboard.defs ) ) return;
  if( g_dashboard.defs.length === 0 ) {
    delete g_dashboard.defs;
    return;
  }

  var def = g_dashboard.defs.shift();
  addPanel( def ).then(function( panel ) {
    loadAllPanels();
  });
}


function addPanelLogout( organizations ) {
  var logout = new Logout({
    userName: WS.userName,
    organizations: organizations
  });
  g_viewLogout = logout;
  var pm = PM( logout );
  pm.on( "actionLogout", actionLogout );
  pm.on( "actionShowStructures", actionShowStructures );
  pm.on( "actionNewOrga", actionNewOrga );
  pm.on( "actionDelOrga", actionDelOrga );
  var panel = new Panel({ content: logout });
  $.add( "BODY", panel );
}


function actionLogout() {
  Splash.close();
  window.setTimeout(function() {
    WS.logout();
    clear();
    location.reload();
  }, 1000);
}

/**
 * @param {string} def
 * * `{ type: 'ORG', id: <orgaId> }`
 */
function addPanel( def ) {
  return new Promise(function (resolve, reject) {
    var panel = findPanel( def );
    if( panel ) {
      panel.refresh();
      resolve( panel );
      return;
    }

    var view = null;
    
    switch( def.type ) {
    case 'ORG': view = createViewOrg( def ); break;
    case 'PATIENTS-LIST': view = createViewPatientsList( def ); break;
    case 'STATS-OCCURENCES': view = createViewStatsOccurences( def ); break;
    default:
      console.error("Unable to parse panel's definition: ", def);
    }

    if( view ) {
      createUnpinnedPanel( view ).then(function( panel ) {
        panel.def = JSON.stringify( def );
        g_dashboard.panels.push({ def: def, view: panel });
        saveDashboard();
        resolve( panel );
      });
    }
    else {
      resolve( null );
    }
  });
}

function findPanel( def ) {
  var key = JSON.stringify( def );
  var panels = g_dashboard.panels.filter(function( panelDef ) {
    // panelDef.def
    // panelDef.view
    return key == JSON.stringify( panelDef.def );
  });
  if( panels.length === 0 ) return null;
  return panels[0].view;
}

function saveDashboard() {
  var dashboard = {
    options: g_dashboard.options,
    panels: g_dashboard.panels.map(function( item ) {
      return item.def;
    })
  };
  SvcDashboard.set( dashboard );
}

function actionShowStructures( orgaId ) {
  addPanel({ type: 'ORG', id: orgaId });
}

function createViewOrg( def ) {
  var orgaId = def.id;
  var orgas = g_organizations.filter(function( orga ) {
    return orga.id == orgaId;
  });
  var orgaName = orgas[0].name;

  var view = new Structures({ id: orgaId, name: orgaName });
  return view;
}


function actionNewOrga() {
  var OrgaView = require("soin.view.orga");
  var view = new OrgaView();
  Dialog.edit({
    title: _('new-orga'),
    content: view,
    action: createOrga.bind( view )
  });
}


/**
 * @param {string} def.type - "PATIENTS-LIST"
 * @param {number} def.carecenter - Id of the care center.
 */
function createViewPatientsList( def ) {
  var carecenterId = parseInt( def.carecenter );
  var view = new PatientsList({ id: carecenterId });
  return view;
}


/**
 * @param {string} def.type - "STATS-OCCURENCES"
 * @param {string} def.carecenterName - Name of the care center.
 * @param {number} def.carecenterId - Id of the care center.
 * @param {string} def.begin - Date of the first day of the month.
 */
function createViewStatsOccurences( def ) {
  var view = new StatsOccurences({
    id: def.carecenterId,
    name: def.carecenterName,
    begin: def.begin
  });
  return view;
}


/**
 * @param {object} view.
 * @resolve panel.
 */
function createUnpinnedPanel( view ) {
  return new Promise(function (resolve, reject) {
    var panel = new Panel({ content: view, pinned: false });
    $.add( "BODY", panel );
    var pm = PM( panel );
    pm.on( "actionRefresh", function() {
      panel.refresh();
    } );
    pm.on( "actionClose", function() {
      closePanel( panel );
    } );

    function onUptodate() {
      PM( view ).off( 'actionUptodate', onUptodate );
      resolve( panel );
    }
    PM( view ).on( 'actionUptodate', onUptodate );
    PM( view ).on( 'actionAddPanel', addPanel );

    panel.refresh();
  });
}


function closePanel( panel ) {
  var stringifiedDef = panel.def;
  g_dashboard.panels = g_dashboard.panels.filter(function( panelStruct ) {
    return JSON.stringify( panelStruct.def ) != stringifiedDef;
  });
  saveDashboard();
  $.detach( panel );
}
/**
 * The orga is already deleted in the database. We just need to remove
 * it from display.
 */
function actionDelOrga( orgaId ) {
  g_organizations = g_organizations.filter(function( orga ) {
    return orga.id != orgaId;
  });
  updateOrganizations();
}


/**
 * @param {string} view.name - Organization's name.
 */
function createOrga( view ) {
  if( !checkOrganizationName( view.name ) ) return;
  var wait = Dialog.wait(_("adding-orga"));
  var name = view.name.trim();
  SvcOrga.add( name ).then(
    function( orgaId ) {
      wait.detach();
      g_organizations.push({
        id: orgaId,
        name: name,
        carecenters: []
      });
      updateOrganizations();
    }, function( errcode ) {
      wait.detach();
      console.error("Unable to add organization: ", errcode);
    }
  );
}


function checkOrganizationName( name ) {
  if( typeof name !== 'string' ) name = '';
  name = name.trim();
  if( name.length < 1 ) return false;
  if( name.length < 2 ) return alertNameTooShort( name );
  if( getAllLowerCaseOrganizationNames().indexOf( name.toLowerCase() ) !== -1 )
    return alertOrganizationNameAlreadyExist( name );

  return true;
}


function getAllLowerCaseOrganizationNames() {
  return g_organizations.map(function( orga ) {
    return orga.name.trim().toLowerCase();
  });
}


function alertNameTooShort( name ) {
  Dialog.alert( _('name-too-short', name) );
  return false;
}


function alertOrganizationNameAlreadyExist( name ) {
  Dialog.alert( _('orga-already-exist', name) );
  return false;
}


/**
 * @param {object} organizations - `[{id, name, carecenters:[{id, name}, ...]}, ...]`
 */
function updateOrganizations( organizations ) {
  if( typeof organizations !== 'undefined' ) g_organizations = organizations;
  g_viewLogout.organizations = g_organizations.slice();
}
