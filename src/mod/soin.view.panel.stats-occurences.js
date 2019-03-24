"use strict";


var CODE_BEHIND = { refresh, toDate, onExportPatients, onExportData };


//############################################################

const FileAPI = require("tfw.fileapi");

var SvcStats = require("soin.svc-occurences");


function refresh() {
  const that = this;
  const d = new Date( 1000 * this.begin );
  d.setMonth( d.getMonth() + 1 );
  const end = Math.floor( d.getTime() * 0.001 );
  this.end = end;
  
  SvcStats.list( this.id, this.begin, end ).then(
    function( data ) {
      that.patients = data.patients;
      that.data = data.data;
      that.actionUptodate = 1;
      that.patientsCount = Object.keys(that.patients).length;
      that.consultationsCount = Object.values(that.patients)
        .map( item => item.$adm )
        .reduce( (acc, cur) => acc + cur );
    }
  );  
}


function toDate( value ) {
  const d = new Date( 1000 * value );
  return d.toLocaleString();
}


function onExportPatients() {
  const keys = {};
  Object.keys(this.patients).forEach(id => {
    const patient = this.patients[id];
    Object.keys(patient).forEach(key => {
      keys[key] = "";
    });
  });

  let content = Object.keys(keys).map(JSON.stringify).join("\t");
  
  Object.keys(this.patients).forEach(id => {
    const patient = this.patients[id];
    const row = Object.assign( keys, patient );
    content += "\n" + Object.keys(row).map(k => JSON.stringify( convert( k, row[k] ) )).join("\t");
  });

  FileAPI.saveAs( new Blob([ content ]), 'demographic.xls' );
}


function onExportData() {
  let content = '"Nom"\t"Valeur"\t"Occurences"';
  Object.keys( this.data ).forEach( name => {
    const row = this.data[name];
    Object.keys( row ).forEach( value => {
      content += `\n"${name}"\t"${value}"\t"${row[value]}"`;
    } )
  });

  FileAPI.saveAs( new Blob([ content ]), 'data.xls' );
}


function convert( key, val ) {
  if( key === '#PATIENT-BIRTH' ) {
    const d = new Date( 1000 * parseInt(val, 10) );
    return `${d.getFullYear()}/${d.getMonth() + 1}/${d.getDate()}`;
  }
  return val;
}
