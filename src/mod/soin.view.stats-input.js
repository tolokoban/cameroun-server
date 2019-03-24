// Code behind.
"use strict";

var CODE_BEHIND = { init };


//############################################################

var SvcStructure = require("soin.svc-structure");
var StructureParser = require("soin.structure-parser");


function init() {
  const d = new Date();
  d.setDate( 1 );
  d.setHours( 0 );
  d.setMinutes( 0 );
  d.setSeconds( 0 );
  d.setMilliseconds( 0 );
  const begin = Math.floor(d.getTime() * 0.001);
  const keys = [];
  const items = [];

  for( let k=0; k<36; k++) {
    keys.push( Math.floor(d.getTime() * 0.001) );
    items.push( `${_('month-' + d.getMonth())} ${d.getFullYear()}` );
    d.setMonth( d.getMonth() - 1 );
  }

  this.items = items;
  this.keys = keys;
  this.begin = begin;
}
