"use strict";

/**
 * @param {number} orgaId
 * @resolve {array} `[{ id, name, code }, ...]`
 */
exports.list = list;
/**
 * @param {number} orgaId
 * @param {string} name
 * @param {string} code
 * @param {number} structureId
 * @resolve {number} Id of the new carecenter.
 */
exports.add = add;
/**
 * @param {number} carecenterId
 */
exports.listPatients = listPatients;


//############################################################

var WebService = require("tfw.web-service");


function list( orgaId ) {
  return new Promise(function (resolve, reject) {
    WebService.get( "carecenter.list", orgaId ).then(
      function( carecenters ) {
        console.info("[soin.svc-carecenter] list:", carecenters);
        if( typeof carecenters === 'number' ) {
          console.error(Error("[list(" + orgaId + ")] Error code: " + carecenters));
        }
        else {
          resolve( carecenters );
        }
      },
      reject
    );
  });
}

function add( orgaId, name, code, structureId ) {
  return new Promise(function (resolve, reject) {
    WebService.get( "carecenter.add", {
      orga: orgaId, name: name, code: code, structure: structureId
    }).then(
      function( retcode ) {
        if( retcode < 1 ) reject( retcode );
        else resolve( retcode );
      },
      reject
    );
  });
}

function listPatients( carecenterId ) {
  return new Promise(function (resolve, reject) {
    WebService.get( "carecenter.listPatients", carecenterId ).then(
      function( data ) {
        resolve( data );
      },
      function( err ) {
        reject( err );
      }
    );
  });
}
