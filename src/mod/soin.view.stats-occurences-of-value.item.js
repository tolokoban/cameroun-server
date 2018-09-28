"use strict";

var CODE_BEHIND = {
  percentConverter: percentConverter
};


//############################################################

var $ = require("dom");


function percentConverter( percent ) {
  var result = "0%";
  if( this.maxPercent > 0 ) {
    result = (100 * percent / this.maxPercent).toFixed(1) + "%";
  }
  $.css( this.$elements.bar, {
    width: result
  });
  return (100 * percent).toFixed(1) + " %";
}
