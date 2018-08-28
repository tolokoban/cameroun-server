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
/**
 * @param {number} structureId
 * @param {string} value
 * @reolve {number} 0 if ok.
 */
exports.setExams = set.bind( exports, "exams" );

/**
 * @param {number} structureId
 * @resolve {string}
 */
exports.getVaccins = get.bind( exports, "vaccins" );
/**
 * @param {number} structureId
 * @param {string} value
 * @reolve {number} 0 if ok.
 */
exports.setVaccins = set.bind( exports, "vaccins" );

/**
 * @param {number} structureId
 * @resolve {string}
 */
exports.getPatient = get.bind( exports, "patient" );
/**
 * @param {number} structureId
 * @param {string} value
 * @reolve {number} 0 if ok.
 */
exports.setPatient = set.bind( exports, "patient" );

/**
 * @param {number} structureId
 * @resolve {string}
 */
exports.getForms = get.bind( exports, "forms" );
/**
 * @param {number} structureId
 * @param {string} value
 * @reolve {number} 0 if ok.
 */
exports.setForms = set.bind( exports, "forms" );

/**
 * @param {number} structureId
 * @resolve {string}
 */
exports.getTypes = get.bind( exports, "types" );
/**
 * @param {number} structureId
 * @param {string} value
 * @reolve {number} 0 if ok.
 */
exports.setTypes = set.bind( exports, "types" );




//############################################################

var WebService = require("tfw.web-service");


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
  return WebService.get( "structure.def", { fld: field, id: structureId } );
}


function set( field, structureId, value ) {
  if( typeof field !== 'string' )
    throw Error("[soin.svc-structure/get(field, structureId)] `field` must be a string but we got: "
               + JSON.stringify(field));
  if( typeof structureId !== 'number' )
    throw Error("[soin.svc-structure/get(field, structureId)] `structureId` must be a number but we got: "
               + JSON.stringify(structureId));
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
