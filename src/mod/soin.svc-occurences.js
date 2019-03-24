"use strict";

module.exports = { list };

const WebService = require("tfw.web-service");

/**
 * For a given range ([begin, end[) return a list of occurences of values of all data.
 * @param {number} begin - Number of seconds since UNIX epoch.
 * @param {number} end - Number of seconds since UNIX epoch.
 * @return
 * {
 *   "key1": {
 *     "val1": 32,
 *     "val2": 13,
 *     "val3": 47,
 *     ...
 *   },
 *   ...
 * }
 */
function list( carecenterId, begin, end ) {
  return WebService.get( "extract", { carecenter: carecenterId, begin, end } );
}
