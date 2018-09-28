"use strict";


var CODE_BEHIND = {
  refresh: refresh
};


//############################################################


var SvcStats = require("soin.svc-stats");


function refresh() {
  var that = this;

  SvcStats.listOccurencesOfValue( this.id, this.field ).then(
    function( data ) {
      that.stats = data.items;
      that.actionUptodate = 1;
    }
  );  
}
