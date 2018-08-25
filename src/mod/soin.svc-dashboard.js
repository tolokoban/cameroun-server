"use strict";

/**
 * No param.
 * @resolve `{ options: {...}, panels: [{type, ...}, ...] }`
 */
exports.get = get;
/**
 * @param {object} dashboard - `{ options: {...}, panels: [{type, ...}, ...] }`
 */
exports.set = set;


//############################################################

var WebService = require("tfw.web-service");


function get() {
  return WebService.get("dashboard.get");
}


function set( dashboard ) {
  return WebService.get("dashboard.set", dashboard);
}
