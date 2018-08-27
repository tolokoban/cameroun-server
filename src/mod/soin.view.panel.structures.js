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
var Structure = require("soin.view.structure");
var SvcStructure = require("soin.svc-structure");
var StructureControl = require("soin.view.structure-control");
var CarecenterControl = require("soin.view.carecenter-control");


function onRefresh() {
  var that = this;

  SvcStructure.list( this.id ).then(function( structures ) {
    that.structures = structures;
    that.actionUptodate = 1;
  });
}


function onAddStructure() {
  Dialog.edit({
    title: _('add-structure'),
    content: new Structure({ name: '' }),
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
  Dialog.edit({
    title: _('add-carecenter'),
    content: new Carecenter({ orga: this.id, name: '', code: makeCode() }),
    action: actionAddCarecenter.bind( this )
  });
}


function actionAddCarecenter( view ) {
  var that = this;

  var carecenterName = view.name;
  Dialog.wait(_('adding-carecenter'), SvcCarecenter.add( this.id, carecenterName ).then(function( carecenterId ) {
    that.carecenters.push({
      id: carecenterId, name: carecenterName
    });
  }));
}


function mapCarecenter( carecenter, more ) {
  var view = new CarecenterControl({ id: carecenter.id });
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
    code += ALPHABET.charAt(Math.floor( Math.random(ALPHABET.length) ) );
  }
  return code;
}
