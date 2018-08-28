// Code behind.
"use strict";

var CODE_BEHIND = {
  getKeys: getKeys,
  getItems: getItems
};


function getKeys( structures ) {
  return structures.map(function( structure ) {
    return structure.id;
  });
}

function getItems( structures ) {
  return structures.map(function( structure ) {
    return structure.name;
  });
}
