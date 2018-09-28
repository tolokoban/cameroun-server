// Code behind.
"use strict";

var CODE_BEHIND = {
  onCarecenterIdChanged: onCarecenterIdChanged,
  onFieldNameChanged: onFieldNameChanged
};


//############################################################

var SvcStructure = require("soin.svc-structure");
var StructureParser = require("soin.structure-parser");


function onCarecenterIdChanged( carecenterId ) {
  var that = this;
  SvcStructure.getForms( carecenterId ).then(
    function( formsDef ) {
      var forms = StructureParser.parse( formsDef );
      var list = StructureParser.flattenFormsFields( forms );
      console.info("[soin.view.stats-input] list=", list);
      that.items = list.map(function( item ) {
        switch( item.path.length ) {
        case 0: return item.caption;
        case 1: return item.path[0] + " / " + item.caption;
        case 2: return item.path[0] + " / " + item.path[1] + " / " + item.caption;
        default: return item.path[0] + " /.../ " + item.path[item.path.length - 1] + " / " + item.caption;
        }
      });
      that.keys = list.map(function( item ) {
        return item.id;
      });
      console.info("[soin.view.stats-input] that.fieldsList=", that.fieldsList);
    }
  );
}


var SvcStats = require("soin.svc-stats");

function onFieldNameChanged( fieldName ) {
  var that = this;

  SvcStats.listOccurencesOfValue( this.carecenterId, fieldName ).then(
    function( data ) {
      console.info("[soin.view.stats-input] data=", data);
      that.stats = data.items;
    }
  );
}
