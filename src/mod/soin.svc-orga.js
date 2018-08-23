"use strict";

/**
 * No param.
 */
exports.list = list;
/**
 * @param {string} name
 * @resolve {number} ID of the new organization.
 */
exports.add = add;

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
      resolve( orgaId );
    }, reject);
  });
}
