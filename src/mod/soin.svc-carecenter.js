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
 * @resolve {number} Id of the new carecenter.
 */
exports.add = add;


//############################################################

var WebService = require("tfw.web-service");


function list( orgaId ) {
  return new Promise(function (resolve, reject) {
    WebService.get( "carecenter.list", orgaId ).then(
      function( carecenters ) {
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

function add( orgaId, name, code ) {
  return new Promise(function (resolve, reject) {
    WebService.get( "carecenter.add", { orga: orgaId, name: name, code: code } ).then(function( retcode ) {
      if( retcode < 1 ) reject( retcode );
      else resolve( retcode );
    }, reject);
  });
}
