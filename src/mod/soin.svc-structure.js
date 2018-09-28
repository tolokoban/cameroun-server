"use strict";

/**
 * @param {number} orgaId
 * @resolve {array} `[{ id, name, exams, vaccins, patient, forms, types }, ...]`
 */
exports.list = list;
/**
 * @param {number} orgaId
 * @param {string} name
 * @resolve {number} Id of the new structure.
 */
exports.add = add;
/**
 * @param {string} name - exams, vaccins, patient, forms or types.
 * @param {number} structureId
 * @resolve {string}
 */
exports.getDef = get;
/**
 * @param {string} name - exams, vaccins, patient, forms or types.
 * @param {number} structureId
 * @param {string} value
 * @reolve {number} 0 if ok.
 */
exports.setDef = set;
/**
 * @param {number} structureId
 * @resolve {string}
 */
exports.getExams = get.bind( exports, "exams" );
exports.getVaccins = get.bind( exports, "vaccins" );
exports.getPatient = get.bind( exports, "patient" );
exports.getForms = get.bind( exports, "forms" );
exports.getTypes = get.bind( exports, "types" );
/**
 * @param {number} structureId
 * @param {string} value
 * @reolve {number} 0 if ok.
 */
exports.setExams = set.bind( exports, "exams" );
exports.setVaccins = set.bind( exports, "vaccins" );
exports.setPatient = set.bind( exports, "patient" );
exports.setForms = set.bind( exports, "forms" );
exports.setTypes = set.bind( exports, "types" );



//############################################################

var WebService = require("tfw.web-service");
var Cache = require("tfw.cache").TimeToLeave;


var g_cache = new Cache( 60000 );



function list( orgaId ) {
  return new Promise(function (resolve, reject) {
    WebService.get( "structure.list", orgaId ).then(
      function( structures ) {
        console.info("[soin.svc-structure] list:", list);
        if( typeof structures === 'number' ) {
          console.error(Error("[list(" + orgaId + ")] Error code: " + structures));
        }
        else {
          resolve( structures );
        }
      },
      reject
    );
  });
}


function get( field, structureId ) {
  if( typeof field !== 'string' )
    throw Error("[soin.svc-structure/get(field, structureId)] `field` must be a string but we got: "
                + JSON.stringify(field));
  if( typeof structureId !== 'number' )
    throw Error("[soin.svc-structure/get(field, structureId)] `structureId` must be a number but we got: "
                + JSON.stringify(structureId));
  return new Promise(function (resolve, reject) {
    var key = field + "/" + structureId;
    var value = g_cache.get( key );
    if( typeof value === 'undefined' ) {
      WebService.get( "structure.def", { fld: field, id: structureId } ).then(
        function( value ) {
          g_cache.set( key, value );
          resolve( value );
        },
        reject
      );
    }
    else {
      resolve( value );
    }
  });
}


function set( field, structureId, value ) {
  if( typeof field !== 'string' )
    throw Error("[soin.svc-structure/get(field, structureId)] `field` must be a string but we got: "
                + JSON.stringify(field));
  if( typeof structureId !== 'number' )
    throw Error("[soin.svc-structure/get(field, structureId)] `structureId` must be a number but we got: "
                + JSON.stringify(structureId));
  var key = field + "/" + structureId;
  g_cache.set( key, value );
  return WebService.get( "structure.def", { fld: field, id: structureId, value: value } );
}


function add( orgaId, name ) {
  return new Promise(function (resolve, reject) {
    WebService.get( "structure.add", { id: orgaId, name: name } ).then(function( retcode ) {
      if( retcode < 1 ) reject( retcode );
      else resolve( retcode );
    }, reject);
  });
}
