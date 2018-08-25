// Code behind.
"use strict";

var CODE_BEHIND = {
  onRefresh: onRefresh,
  mapOrga: mapOrga,
  mapOrgaHeader: mapOrgaHeader
};


var $ = require("dom");
var PM = require("tfw.binding.property-manager");
var Button = require("tfw.view.button");
var Expand = require("tfw.view.expand");
var OrgaControl = require("soin.view.orga-control");


function onRefresh() {
  var that = this;

  window.setTimeout(function() {
    that.actionUptodate = 1;
  });
}


function mapOrga( orga, more ) {
  var that = this;

  var content = new OrgaControl( orga );
  var expand = new Expand({ label: orga.name, content: content });
  var pm = PM( content );
  pm.on('name', function(v) {
    expand.label = v;
  });
  pm.on('actionDelete', function( id ) {
    that.actionDelOrga = id;
  });
  pm.on('actionShowStructures', function( id ) {
    that.actionShowStructures = id;
  });
  return expand;
}


function mapOrgaHeader( list, context ) {
  if( list.length == 0 ) return createDivTellingThereIsNoOrga();
  context.root = $.tag('div');
  return context.root;
}


function createDivTellingThereIsNoOrga() {
  return $.tag('p', [_('no-orga')]);
}
