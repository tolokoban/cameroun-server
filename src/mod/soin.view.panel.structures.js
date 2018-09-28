// Code behind.
"use strict";

var CODE_BEHIND = {
  onRefresh: onRefresh,
  onAddStructure: onAddStructure,
  onAddCarecenter: onAddCarecenter,
  mapStructure: mapStructure,
  headerStructure: headerStructure,
  mapCarecenter: mapCarecenter,
  headerCarecenter: headerCarecenter
};


var $ = require("dom");
var PM = require("tfw.binding.property-manager");
var Button = require("tfw.view.button");
var Expand = require("tfw.view.expand");
var Dialog = require("soin.dialog");
var Carecenter = require("soin.view.carecenter");
var SvcStructure = require("soin.svc-structure");
var SvcCarecenter = require("soin.svc-carecenter");
var StructureView = require("soin.view.structure");
var StatsInputView = require("soin.view.stats-input");
var StructureControl = require("soin.view.structure-control");
var CarecenterControl = require("soin.view.carecenter-control");

function onRefresh() {
  var that = this;

  SvcStructure.list( this.id ).then(function( structures ) {
    that.structures = structures;
    return SvcCarecenter.list( that.id );
  }).then(function( carecenters ) {
    that.carecenters = carecenters;
    that.actionUptodate = 1;
  });
}


function onAddStructure() {
  Dialog.edit({
    title: _('add-structure'),
    content: new StructureView({ name: '' }),
    action: actionAddStructure.bind( this )
  });
}


function actionAddStructure( view ) {
  var that = this;

  var structureName = view.name;
  if( existsStructureWithThisName.call( this, structureName ) )
    return Dialog.alert(_('name-exists'));
  Dialog.wait(_('adding-structure'), SvcStructure.add( this.id, structureName ).then(function( structureId ) {
    that.structures.push({
      id: structureId, name: structureName
    });
  }));
}


function existsStructureWithThisName( name ) {
  var nameToCompare = name.trim().toLowerCase();
  var structuresWithSameName = this.structures.filter(function( structure ) {
    return structure.name.trim().toLowerCase() == nameToCompare;
  });

  return structuresWithSameName.length > 0;
}


function mapStructure( structure, more ) {
  var view = new StructureControl({ id: structure.id });
  var expand = new Expand({ label: structure.name, content: view });
  return expand;
}


function headerStructure( structures ) {
  if( structures.length === 0 ) return $.div([_("no-structure")]);
}


function onAddCarecenter() {
  var structure = "";
  if( this.structures && this.structures.length > 0 ) {
    structure = this.structures.get(0).id;
  }
  Dialog.edit({
    title: _('add-carecenter'),
    content: new Carecenter({
      orga: this.id,
      name: '',
      code: makeCode(),
      structures: this.structures,
      structure: structure
    }),
    action: actionAddCarecenter.bind( this )
  });
}


function actionAddCarecenter( view ) {
  var that = this;

  var carecenterName = view.name;
  var carecenterCode = view.code;
  var carecenterStructure = view.structure;

  Dialog.wait(_('adding-carecenter'), SvcCarecenter.add(
    this.id, carecenterName, carecenterCode, carecenterStructure
  ).then(
    function( carecenterId ) {
      that.carecenters.push({
        id: carecenterId, name: carecenterName, code: carecenterCode
      });
    }
  ));
}


function mapCarecenter( carecenter, more ) {
  var that = this;

  var view = new CarecenterControl({ id: carecenter.id, code: carecenter.code });
  PM( view ).on( 'actionPatients', function() {
    that.actionAddPanel = {
      type: 'PATIENTS-LIST',
      carecenter: carecenter.id
    };
  });
  PM( view ).on( 'actionStats', createStatsPanel.bind( this, carecenter ) );
  var expand = new Expand({ label: carecenter.name, content: view });
  return expand;
}


function headerCarecenter( carecenters ) {
  if( carecenters.length === 0 ) return $.div([_("no-carecenter")]);
}

var ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
function makeCode() {
  var code = '';
  for( var i=0; i<10; i++ ) {
    code += ALPHABET.charAt(Math.floor( Math.random() * ALPHABET.length ) );
  }
  return code;
}


function createStatsPanel( carecenter ) {
  var that = this;

  var view = new StatsInputView({ carecenterId: carecenter.id });
  Dialog.confirm({
    title: carecenter.name,
    content: view,
    confirm: _('create'),
    action: function( view ) {
      var panelDef = {
        type: 'STATS-OCCURENCES',
        carecenterName: carecenter.name,
        carecenterId: view.carecenterId,
        fieldName: view.fieldName
      };
      that.actionAddPanel = panelDef;
    }
  });
}
