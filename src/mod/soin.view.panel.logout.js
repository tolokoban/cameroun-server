// Code behind.
"use strict";

var CODE_BEHIND = {
  mapOrga: mapOrga,
  mapOrgaHeader: mapOrgaHeader
};


var $ = require("dom");
var Button = require("tfw.view.button");
var Expand = require("tfw.view.expand");

function mapOrga( orga, more ) {
  var root = more.context.root;
  var btnRename = new Button({
    text: _('rename'), icon: 'edit', type: 'primary'
  });
  var btnDelete = new Button({
    text: _('delete'), icon: 'delete', type: 'secondary'
  });
  var content = $.div('main-flex', [
    btnRename, btnDelete
  ]);
  return new Expand({ label: orga.name, content: content });
}


function mapOrgaHeader( list, context ) {
  if( list.length == 0 ) return createDivTellingThereIsNoOrga();
  context.root = $.tag('div');
  return context.root;
}


function createDivTellingThereIsNoOrga() {
  return $.tag('p', [_('no-orga')]);
}
