"use strict";

var CODE_BEHIND = {
  onRename: onRename,
  onDelete: onDelete,
  onPatient: onPatient,
  onTypes: onTypes,
  onForms: onForms,
  onVaccins: onVaccins,
  onExams: onExams
};



var $ = require("dom");
var Dialog = require("soin.dialog");
var Textarea = require("tfw.view.textarea");
var SvcStructure = require("soin.svc-structure");
var StructureParser = require("soin.structure-parser");

function onRename() {

}


function onDelete() {
  
}


function onPatient() {
  editDefinition.call( this, "patient" );
}


function onTypes() {
  editDefinition.call( this, "types" );
}


function onForms() {
  editDefinition.call( this, "forms" );
}


function onVaccins() {
  editDefinition.call( this, "vaccins" );
}


function onExams() {
  editDefinition.call( this, "exams" );
}


function editDefinition( name ) {
  var view = new Textarea({ label: _(name), width: '100%', height: '100%' });
  $.addClass( view, "soin-view-structure-control" );
  Dialog.edit({
    title: _(name),
    confirm: _('ok'),
    content: view,
    action: saveDefinition.bind( this, name ),
    validator: validator.bind( this )
  });
  Dialog.wait(_('loading'), SvcStructure.getDef( name, this.id )).then(
    function( value ) {
      view.value = value;
      view.focus = true;
    }
  );
}


function saveDefinition( name, textarea ) {
  SvcStructure.setDef( name, this.id, textarea.value );
}


function validator( textarea ) {
  return new Promise(function (resolve, reject) {
    var code = textarea.value;
    try {
      StructureParser.parse( code );
      resolve();
    }
    catch( ex ) {
      textarea.gotoLine( ex.lineNumber + 1 );
      textarea.focus = true;
      Dialog.alert({ content: parseErrorToDom( ex, code ) });
      console.info("[soin.view.structure-control] ex=", ex);
      reject( ex );
    }
  });
}


/**
 * @param {number} error.lineNumber
 * @param {string} error.message
 * @param {string} code
 */
function parseErrorToDom( error, code ) {
  var lines = code.split("\n");
  var lineNum = error.lineNumber;
  var lineMin = Math.max(1, lineNum - 4);
  var lineMax = Math.min(lines.length + 1, lineNum + 4);
  lines = lines.slice( lineMin - 1, lineMax );
  var divCode = $.div('soin-view-structure-control-table');
  lines.forEach(function (line, index) {
    var cls = index % 2 ? 'thm-bg0' : 'thm-bg3';
    if( index + lineMin === lineNum ) cls = 'thm-bgSL';
    var row = $.div( cls, [
      $.div('label', [ index + lineMin ]),
      $.div([ line ])
    ]);
    $.add( divCode, row );
  });

  return $.div([
    $.tag('p', [error.message]),
    divCode
  ]);
}
