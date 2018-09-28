"use strict";

/**
 * @param {number} carecenterId
 * @param {string} fieldName
 * @return {
 *   total: 458,
 *   items: [
 *     { value: "", count: 203, percent: 0.4432314410480349 },
 *     { value: "Yes", count: 145, percent: 0.3165938864628821 },
 *     { value: "No", count: 110, percent: 0.4432314410480349 }
 *   ]
 * }
 */
exports.listOccurencesOfValue = listOccurencesOfValue;


//############################################################


var WebService = require("tfw.web-service");


function listOccurencesOfValue( carecenterId, fieldName ) {
  return new Promise(function (resolve, reject) {
    WebService.get("stats", { carecenter: carecenterId, field: fieldName }).then(
      function( data ) {
        var list = [];
        var count = 0;
        Object.keys( data ).forEach(function (value) {
          var occurences = data[value];
          count += occurences;
          list.push({
            value: value, count: occurences, percent: 0
          });
        });
        list.sort(function(a, b) { return b.count - a.count; });
        if( count > 0 ) {
          list.forEach(function (item) {
            item.percent = item.count / count;
          });
        }
        resolve({
          total: count,
          items: list
        });
      },
      reject
    );
  });
}
