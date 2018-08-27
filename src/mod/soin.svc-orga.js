"use strict";

/**
 * No param.
 * @resolve `[{id, name, carecenters:[{id, name}, ...]}, ...]`.
 */
exports.list = list;
/**
 * @param {string} name
 * @resolve {number} ID of the new organization.
 */
exports.add = add;
/**
 * @param {number} id
 * @param {string} name
 */
exports.rename = rename;
/**
 * @param {number} id
 */
exports.delete = del;

exports.NOT_GRANTED = -1;
exports.UNKNOWN_ERROR = -2;

//############################################################

var WebService = require("tfw.web-service");


function list() {
  return new Promise(function (resolve, reject) {
    WebService.get("orga.list").then(function(data) {
      resolve( data );
    }, reject);
  });
}


function add( name ) {
  return new Promise(function (resolve, reject) {
    WebService.get("orga.add", { name: name }).then(function(orgaId) {
      if( orgaId > 0 ) resolve( orgaId );
      else reject( exports.NOT_GRANTED );
    }, reject);
  });
}


function rename( id, name ) {
  return new Promise(function (resolve, reject) {
    WebService.get("orga.rename", { id: id, name: name }).then(function(retcode) {
      switch( retcode ) {
      case 0:
        resolve(); break;
      case -1:
        reject( exports.NOT_GRANTED ); break;
      default:
        console.error("Unknown error: ", retcode);
        reject( exports.UNKNOWN_ERROR );
      }
    }, reject);
  });
}


function del( id ) {
  return new Promise(function (resolve, reject) {
    WebService.get("orga.delete", { id: id }).then(function(retcode) {
      switch( retcode ) {
      case 0:
        resolve(); break;
      case -1:
        reject( exports.NOT_GRANTED ); break;
      default:
        console.error("Unknown error: ", retcode);
        reject( exports.UNKNOWN_ERROR );
      }
    }, reject);
  });
}
