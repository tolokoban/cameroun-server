/** @module tfw.view.icon */require( 'tfw.view.icon', function(require, module, exports) { var _=function(){var D={"en":{},"fr":{}},X=require("$").intl;function _(){return X(D,arguments);}_.all=D;return _}();
    "use strict";

var $ = require( "dom" );
var Icons = require("tfw.icons");


// Code behind to use in the XJS.
var CODE_BEHIND = {
  onContentChanged: onContentChanged,
  onPen0Changed: function(v) { updatePen.call( this, 0, v ); },
  onPen1Changed: function(v) { updatePen.call( this, 1, v ); },
  onPen2Changed: function(v) { updatePen.call( this, 2, v ); },
  onPen3Changed: function(v) { updatePen.call( this, 3, v ); },
  onPen4Changed: function(v) { updatePen.call( this, 4, v ); },
  onPen5Changed: function(v) { updatePen.call( this, 5, v ); },
  onPen6Changed: function(v) { updatePen.call( this, 6, v ); },
  onPen7Changed: function(v) { updatePen.call( this, 7, v ); }
};


function onContentChanged( content ) {
  try {
    if( typeof content === 'string' ) {
      content = Icons.iconsBook[content];
    }

    this._content = createSvgFromDefinition.call( this, content );
    $.clear( this, this._content.svgRootGroup );

    // Update pens' colors.
    [0,1,2,3,4,5,6,7].forEach(function (penIndex) {
      updatePen.call( this, penIndex, this["pen" + penIndex] );
    }, this);

    this.$.style.display = "";
  }
  catch( ex ) {
    this.$.style.display = "none";
    if( this.content != '' ) this.content = '';
  }
}

// Special colors.
// 0 is black,  1 is white, P  is primary, S is secondary,  L is light
// and D is dark.
var FILL_COLORS_TO_CLASSES = {
  '0': "fill0",
  '1': "fill1",
  P:   "thm-svg-fill-P",
  PL:  "thm-svg-fill-PL",
  PD:  "thm-svg-fill-PD",
  S:   "thm-svg-fill-S",
  SL:  "thm-svg-fill-SL",
  SD:  "thm-svg-fill-SD"
};
var STROKE_COLORS_TO_CLASSES = {
  '0': "stroke0",
  '1': "stroke1",
  P:    "thm-svg-stroke-P",
  PL:   "thm-svg-stroke-PL",
  PD:   "thm-svg-stroke-PD",
  S:    "thm-svg-stroke-S",
  SL:   "thm-svg-stroke-SL",
  SD:   "thm-svg-stroke-SD"
};


function createSvgFromDefinition( def ) {
  var svgParent = $.svg( 'g', {
    'stroke-width': 6,
    fill: "none",
    'stroke-linecap': 'round',
    'stroke-linejoin': 'round'
  } );

  // Store elements with special colors  in order to update them later
  // if needed. We can have up to 8 colors numbered from 0 to 5.
  var elementsToFillPerColor = [[], [], [], [], [], [], [], []];
  var elementsToStrokePerColor = [[], [], [], [], [], [], [], []];

  var svgRootGroup = addChild( this.$, elementsToFillPerColor, elementsToStrokePerColor, def );
  $.att( svgRootGroup, {
    'stroke-width': 6,
    fill: "none",
    'stroke-linecap': 'round',
    'stroke-linejoin': 'round'
  });

  return {
    svgRootGroup: svgRootGroup,
    elementsToFillPerColor: elementsToFillPerColor,
    elementsToStrokePerColor: elementsToStrokePerColor
  };
}

/**
 * @param {Node} parent - SVG element into append elements created from `def`.
 * @param {string} def - Text to add to the `parent`.
 * @param {array} def - SVG node to add to `parent`.
 * @param {string} def[0] - Tag name of then SVG node to add to `parent`.
 * @param {array} def[>0] - Definition of the children.
 * @param {object} def[>0] - Attributes of the element.
 */
function addChild( parent, elementsToFillPerColor, elementsToStrokePerColor, def ) {
  if( typeof def === 'string' )
    return $.add( parent, def );
  checkDefinitionSyntax( def );
  var elementName = def[0];
  var element = $.svg( elementName );

  def.forEach(function (childItem, index) {
    if( index === 0 ) return;
    if( Array.isArray( childItem ) ) {
      childItem.forEach( addChild.bind( null, element, elementsToFillPerColor, elementsToStrokePerColor ) );
    } else {
      setAttributesAndRegisterElementsWithSpecialColors(
        element, elementsToFillPerColor, elementsToStrokePerColor, childItem );
    }
  });

  $.add( parent, element );
  return element;
}

function setAttributesAndRegisterElementsWithSpecialColors(
  node, elementsToFillPerColor, elementsToStrokePerColor, attribs ) {
    var attName, attValue, valueAsIndex, elementsPerColor;

    for( attName in attribs ) {
      attValue = attribs[attName];
      if( attName === 'fill' || attName === 'stroke' ) {
        valueAsIndex = parseInt( attValue );
        if( isNaN( valueAsIndex ) ) {
          // Straigth attribute.
          $.att( node, attName, attValue );
        } else {
          elementsPerColor = attName === 'fill' ? elementsToFillPerColor : elementsToStrokePerColor;
          valueAsIndex = clamp(valueAsIndex, 0, elementsPerColor.length - 1 );
          elementsPerColor[ valueAsIndex ].push( node );
        }
      } else {
        $.att( node, attName, attValue );
      }
    }
  }

function checkDefinitionSyntax( def ) {
  if( !Array.isArray( def ) ) {
    throw "Definition of SVG elements must be arrays!\n"
      + JSON.stringify( def );
  }
  var svgElementTagName = def[0];
  if( typeof svgElementTagName !== 'string' )
    throw "The first item of a SVG element must be a string!\n" + svgElementTagName;
}

function updatePen( penIndex, penColor ) {
  var elementsToFill = this._content.elementsToFillPerColor[penIndex];
  if( !Array.isArray(elementsToFill) ) elementsToFill = [];
  var elementsToStroke = this._content.elementsToStrokePerColor[penIndex];
  if( !Array.isArray(elementsToStroke) ) elementsToStroke = [];

  updateColor( elementsToFill, elementsToStroke, penColor );
}

function updateColor( elementsToFill, elementsToStroke, color ) {
  updateColorForType( "fill", elementsToFill, FILL_COLORS_TO_CLASSES, color );
  updateColorForType( "stroke", elementsToStroke, STROKE_COLORS_TO_CLASSES, color );
}

function updateColorForType( attName, elements, classes, color ) {
  var className = classes[color];
  if( typeof className === 'undefined' ) {
    elements.forEach(function (element) {
      $.att( element, attName, color );
    });
  } else {
    elements.forEach(function (element) {
      Object.values( classes ).forEach(function (classNameToRemove) {
        $.removeClass( element, classNameToRemove );
      });
      $.addClass( element, className );
      $.removeAtt( element, attName );
    });
  }
}

function clamp( value, min, max ) {
  if( value < min ) return min;
  if( value > max ) return max;
  return value;
}


//===============================
// XJS:View autogenerated code.
try {
  module.exports = function() {
    //--------------------
    // Dependent modules.
    var $ = require('dom');
    var PM = require('tfw.binding.property-manager');
    var Tag = require('tfw.view').Tag;
    var Link = require('tfw.binding.link');
    var View = require('tfw.view');;
    var Converters = require('tfw.binding.converters');
    //-------------------------------------------------------
    // Check if needed functions are defined in code behind.
    View.ensureCodeBehind( CODE_BEHIND, "onContentChanged", "onPen0Changed", "onPen1Changed", "onPen2Changed", "onPen3Changed", "onPen4Changed", "onPen6Changed", "onPen7Changed" );
    //-------------------
    // Global functions.
    function defVal(args, attName, attValue) { return args[attName] === undefined ? attValue : args[attName]; };
    function addClassIfFalse(element, className, value) {
    if( value ) $.removeClass(element, className);
    else $.addClass(element, className); };;
    function addClassIfTrue(element, className, value) {
    if( value ) $.addClass(element, className);
    else $.removeClass(element, className); };;
    //-------------------
    // Global variables.
    var booleanCast = Converters.get('boolean');
    var stringCast = Converters.get('string');
    var unitCast = Converters.get('unit');
    //-------------------
    // Class definition.
    var ViewClass = function( args ) {
      try {
        if( typeof args === 'undefined' ) args = {};
        this.$elements = {};
        var that = this;
        var pm = PM(this);
        //--------------------
        // Create attributes.
        pm.create("visible", { cast: booleanCast() });
        pm.create("content", { cast: stringCast() });
        pm.create("size", { cast: unitCast() });
        pm.create("animate", { cast: booleanCast() });
        pm.create("flipH", { cast: booleanCast() });
        pm.create("flipV", { cast: booleanCast() });
        pm.create("pen0", { cast: stringCast() });
        pm.create("pen1", { cast: stringCast() });
        pm.create("pen2", { cast: stringCast() });
        pm.create("pen3", { cast: stringCast() });
        pm.create("pen4", { cast: stringCast() });
        pm.create("pen5", { cast: stringCast() });
        pm.create("pen6", { cast: stringCast() });
        pm.create("pen7", { cast: stringCast() });
        //------------------
        // Create elements.
        var e_ = new Tag('SVG', ["class","width","height","viewBox","preserveAspectRatio"]);
        //-----------------------
        // Declare root element.
        Object.defineProperty( this, '$', {value: e_.$, writable: false, enumerable: false, configurable: false } );
        //-------
        // Links
        new Link({
          A:{obj: that, name: 'visible'},
          B:{action: function(v) {
          addClassIfFalse( e_, "hide", v );}}
        });
        new Link({
          A:{obj: that, name: 'animate'},
          B:{action: function(v) {
          addClassIfTrue( e_, "animate", v );}}
        });
        new Link({
          A:{obj: that, name: 'flipH'},
          B:{action: function(v) {
          addClassIfTrue( e_, "flipH", v );}}
        });
        new Link({
          A:{obj: that, name: 'flipV'},
          B:{action: function(v) {
          addClassIfTrue( e_, "flipV", v );}}
        });
        //-----------------------
        // On attribute changed.
        pm.on( "content", function(v) {
          try {
            CODE_BEHIND.onContentChanged.call( that, v );
          }
          catch( ex ) {
            console.error('Exception in function behind "onContentChanged" of module "mod/tfw.view.icon.js" for attribute "content"!  ');
            console.error( ex );
          }} );
        pm.on( "pen0", function(v) {
          try {
            CODE_BEHIND.onPen0Changed.call( that, v );
          }
          catch( ex ) {
            console.error('Exception in function behind "onPen0Changed" of module "mod/tfw.view.icon.js" for attribute "pen0"!  ');
            console.error( ex );
          }} );
        pm.on( "pen1", function(v) {
          try {
            CODE_BEHIND.onPen1Changed.call( that, v );
          }
          catch( ex ) {
            console.error('Exception in function behind "onPen1Changed" of module "mod/tfw.view.icon.js" for attribute "pen1"!  ');
            console.error( ex );
          }} );
        pm.on( "pen2", function(v) {
          try {
            CODE_BEHIND.onPen2Changed.call( that, v );
          }
          catch( ex ) {
            console.error('Exception in function behind "onPen2Changed" of module "mod/tfw.view.icon.js" for attribute "pen2"!  ');
            console.error( ex );
          }} );
        pm.on( "pen3", function(v) {
          try {
            CODE_BEHIND.onPen3Changed.call( that, v );
          }
          catch( ex ) {
            console.error('Exception in function behind "onPen3Changed" of module "mod/tfw.view.icon.js" for attribute "pen3"!  ');
            console.error( ex );
          }} );
        pm.on( "pen4", function(v) {
          try {
            CODE_BEHIND.onPen3Changed.call( that, v );
          }
          catch( ex ) {
            console.error('Exception in function behind "onPen3Changed" of module "mod/tfw.view.icon.js" for attribute "pen4"!  ');
            console.error( ex );
          }} );
        pm.on( "pen5", function(v) {
          try {
            CODE_BEHIND.onPen4Changed.call( that, v );
          }
          catch( ex ) {
            console.error('Exception in function behind "onPen4Changed" of module "mod/tfw.view.icon.js" for attribute "pen5"!  ');
            console.error( ex );
          }} );
        pm.on( "pen6", function(v) {
          try {
            CODE_BEHIND.onPen6Changed.call( that, v );
          }
          catch( ex ) {
            console.error('Exception in function behind "onPen6Changed" of module "mod/tfw.view.icon.js" for attribute "pen6"!  ');
            console.error( ex );
          }} );
        pm.on( "pen7", function(v) {
          try {
            CODE_BEHIND.onPen7Changed.call( that, v );
          }
          catch( ex ) {
            console.error('Exception in function behind "onPen7Changed" of module "mod/tfw.view.icon.js" for attribute "pen7"!  ');
            console.error( ex );
          }} );
        pm.on("size", function(v) {
          e_.$.style["width"] = v;
        });
        pm.on("size", function(v) {
          e_.$.style["height"] = v;
        });
        //----------------------
        // Initialize elements.
        e_.class = "tfw-view-icon";
        e_.width = "100%";
        e_.height = "100%";
        e_.viewBox = "-65 -65 130 130";
        e_.preserveAspectRatio = "xMidYMid meet";
        //------------------------
        // Initialize attributes.
        this.visible = defVal(args, "visible", true);
        this.content = defVal(args, "content", "ok");
        this.size = defVal(args, "size", 28);
        this.animate = defVal(args, "animate", false);
        this.flipH = defVal(args, "flipH", false);
        this.flipV = defVal(args, "flipV", false);
        this.pen0 = defVal(args, "pen0", 0);
        this.pen1 = defVal(args, "pen1", 1);
        this.pen2 = defVal(args, "pen2", "P");
        this.pen3 = defVal(args, "pen3", "PD");
        this.pen4 = defVal(args, "pen4", "PL");
        this.pen5 = defVal(args, "pen5", "S");
        this.pen6 = defVal(args, "pen6", "SD");
        this.pen7 = defVal(args, "pen7", "SL");
        $.addClass(this, 'view', 'custom');
      }
      catch( ex ) {
        console.error('mod/tfw.view.icon.js', ex);
        throw Error('Instantiation error in XJS of "mod/tfw.view.icon.js":\n' + ex)
      }
    };
    return ViewClass;
  }();
}
catch( ex ) {
  throw Error('Definition error in XJS of "mod/tfw.view.icon.js"\n' + ex)
}


  
module.exports._ = _;
/**
 * @module tfw.view.icon
 * @see module:$
 * @see module:dom
 * @see module:tfw.icons
 * @see module:dom
 * @see module:tfw.binding.property-manager
 * @see module:tfw.view
 * @see module:tfw.binding.link
 * @see module:tfw.view
 * @see module:tfw.binding.converters

 */
});