// Code behind.
"use strict";

var CODE_BEHIND = {
  onRefresh: onRefresh,
  onAddStructure: onAddStructure,
  mapStructure: mapStructure,
  header: header
};


var $ = require("dom");
var PM = require("tfw.binding.property-manager");
var Button = require("tfw.view.button");
var Expand = require("tfw.view.expand");
var Dialog = require("soin.dialog");
var Structure = require("soin.view.structure");
var SvcStructure = require("soin.svc-structure");
var StructureControl = require("soin.view.structure-control");


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


function header( structures ) {
  if( structures.length === 0 ) return $.div([_("no-structure")]);
}
