// Code behind.
"use strict";

var CODE_BEHIND = {
  onEdit: onEdit,
  onDelete: onDelete,
  prependId: prependId
};


function onEdit() {

}


function onDelete() {
  
}


function prependId( v ) {
  return this.id + "-" + v;
}
