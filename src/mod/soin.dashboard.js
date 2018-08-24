"use strict";

exports.clear = clear;
exports.refresh = refresh;


//############################################################


var $ = require("dom");
var WS = require("tfw.web-service");
var PM = require("tfw.binding.property-manager");
var Panel = require("soin.view.panel");
var Dialog = require("soin.dialog");
var Splash = require("soin.splash");
var Logout = require("soin.view.panel.logout");
var SvcOrga = require("soin.svc-orga");


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
    SvcOrga.list().then(function( organizations ) {
      g_organizations = organizations;
      addPanelLogout( organizations );
      resolve();
    });
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
  pm.on( "actionNewOrga", actionNewOrga );
  var panel = new Panel({ content: logout });
  $.add( document.body, panel );
}


function actionLogout() {
  Splash.close();
  window.setTimeout(function() {
    WS.logout();
    clear();
    location.reload();
  }, 1000);
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
 * @param {string} view.name - Organization's name.
 */
function createOrga( view ) {
  if( !checkOrganizationName( view.name ) ) return;
  var wait = Dialog.wait(_("adding-orga"));
  var name = view.name.trim();
  SvcOrga.add( name ).then(function( orgaId ) {
    wait.detach();
    g_organizations.push({
      id: orgaId,
      name: name,
      carecenters: []
    });
    updateOrganizations();
  }, wait.detach.bind( wait ));
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
  g_viewLogout.organizations = g_organizations;
}
