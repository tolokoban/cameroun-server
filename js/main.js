/** @module main */require( 'main', function(require, module, exports) { var _=function(){var D={"en":{"welcome":"Welcome in the world of"},"fr":{"welcome":"Bienvenue dans le monde de"}},X=require("$").intl;function _(){return X(D,arguments);}_.all=D;return _}();
    "use strict";

var Install = require("tfw.install");


exports.start = function() {
  Install.check("cameroun", start);
};


function start() {
  
}


  
module.exports._ = _;
/**
 * @module main
 * @see module:$
 * @see module:tfw.install

 */
});