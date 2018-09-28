// Code behind.
"use strict";

var CODE_BEHIND = {
  mapItems: mapItems,
  mapItemsHeader: mapItemsHeader
};

//############################################################

var $ = require("dom");
var Item = require("soin.view.stats-occurences-of-value.item");


function mapItems( item, more ) {
  var view = new Item({
    label: item.value,
    count: item.count,
    percent: item.percent,
    maxPercent: more.context.maxPercent
  });
  return view;
}


function mapItemsHeader( list, context ) {
  if( list.length === 0 ) return;
  context.maxPercent = list[0].percent;
}
