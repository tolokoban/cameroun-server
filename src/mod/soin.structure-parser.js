"use strict";

/**
 * @param {string} definition
 */
exports.parse = parse;
/**
 * @param {object} forms
 * {
 *   CONCLUSION: {
 *     caption: "Conclusion",
 *     children: {
 *       "#SURGERY-GYN": {
 *         caption: "Chirurgie",
 *         id: "#SURGERY-GYN",
 *         type: "#SURGERY-GYN"
 *       }
 *     }
 *   },
 *   ...
 * }
 * @return `[{ id:"#SURGERY-GYN", caption:"Chirurgie", path:["Conclusion", "Chirurgie"] }, ...]`
 */
exports.flattenFormsFields = flattenFormsFields;


/**
 * Le fichier `types.org` définit tous  les types de données complexe.
 * Il  n'y a  pas  de type  très contraignant,  tous  les textes  sont
 * libres, mais on propose des listes pour faciliter la saisie. Chaque
 * élément  de cette  liste est  muni d'un  identifiant (précédé  d'un
 * dièse `#`).
 * Un type peut être hiérarchique. Par exemple, une adresse propose un
 * pays, une région,  un district, ... Les  propositions dépendent des
 * choix  précédents. Ainsi,  la liste  des propositions  de districts
 * dépend de la région sélectionnée.
 *
 * Le  parsing du  fichier `types.org`  se  fait ligne  par ligne.  On
 * ignore tous  les espaces/tabulations en  début de ligne.  Le nombre
 * d'astérisques  `*`  qui  commencent  une ligne  indique  le  niveau
 * hiérarchique.
 * Au  niveau 1,  on trouve  l'identifiant du  type qui  doit toujours
 * commencer par un dièse `#`.
 *
 * Voici un  exemple de  fichier en  entrée et de  comment on  doit le
 * traduire.
 * ```
 * #GENDER
 * * #H Homme
 * * #F Femme
 *
 * #LOCALIZATION
 * * Cameroun
 * ** Littoral
 * *** District 9
 * **** Village 1
 * **** Village 2
 * **** Village 3
 * *** Un peu plus loin
 * **** Village A
 * **** Village B
 * ** Centre
 * *** Pas tout près
 * **** Village Toto
 * *** Au fin fond du...
 * **** Village Alpha
 * **** Village Beta
 * **** Village Gama
 * ```
 *
 * ```
 * {
 *   "#GENDER": {
 *     "#H": { "caption": "Homme" },
 *     "#F": { "caption": "Femme" }
 *   },
 *   "#LOCALIZATION": {
 *     "Cameroun": { "caption": "Cameroun", "children": {
 *       "Littoral": { "caption": "Littoral", "children": {
 *         ...
 *       }},
 *       "Centre": { "caption": "Centre", "children": {
 *         ...
 *       }},
 *       ...
 *     }}
 *   }
 * }
 * ```
 */


var RX_LINE = /^(#[A-Z0-9-]+)?([^\(@]*)(\([^\)]*\)\+?)?(@[A-Z0-9,-]+)?/;


function parse(code) {
  var types = {};
  var levels = [types];
  if( typeof code !== 'string' ) code = '' + code;
  code.split('\n').forEach(function (line, lineNumber) {
    try {
      line = line.trim();
      // Ignorer les lignes vides.
      if (line.length === 0) return;
      // Ignorer les commentaires.
      if (line.substr(0, 2) === '//') return;
      if (line.charAt(0) !== '*') {
        throw _('err-1');
      }

      // Calculer le niveau hiérarchique `level`.
      var level = 0;
      while (line.charAt(0) == '*') {
        line = line.substr(1);
        level++;
      }
      line = line.trim();

      var item;      
      if (level > levels.length) {
        throw _('err-2', line, levels.length - 1, level);
      }
      while (levels.length > level) {
        levels.pop();
      }
      item = parseLine( line );
      if (typeof levels[levels.length - 1][item.id] !== 'undefined') {
        throw _('err-3', item.id);
      }
      levels[levels.length - 1][item.id] = item;
      levels.push( item.children );
    }
    catch (ex) {
      throw { lineNumber: lineNumber + 1, message: ex };
    }
  });
  return types;
};



function parseLine( line ) {
  line = line.trim();
  var item = { children: {} };
  var m = RX_LINE.exec(line);
  if (m[2]) {
    item.caption = m[2].trim();
  }
  if (m[1]) {
    item.id = m[1].trim();
  } else {
    item.id = item.caption.toUpperCase();
  }
  if (m[3]) {
    item.type = m[3].substr(1, m[3].length - 2).trim();
  }
  if (m[4]) {
    item.tags = m[4].trim().substr(1).split(',').map(function(v) { return v.trim(); });
  }

  return item;
}


/**
 * @param {object} forms
 * {
 *   CONCLUSION: {
 *     caption: "Conclusion",
 *     children: {
 *       "#SURGERY-GYN": {
 *         caption: "Chirurgie",
 *         id: "#SURGERY-GYN",
 *         type: "#SURGERY-GYN"
 *       }
 *     }
 *   },
 *   ...
 * }
 * @return `[{ id:"#SURGERY-GYN", caption:"Chirurgie", path:["Conclusion", "Chirurgie"] }, ...]`
 */
function flattenFormsFields( forms ) {
  var list = [];
  recursiveFlattenFormsFields( forms, list, [] );
  list.sort(function(a, b) {
    var captionA = a.caption;
    var captionB = b.caption;
    if( captionA < captionB ) return -1;
    if( captionA > captionB ) return +1;
    return 0;
  });
  return list;
}


function recursiveFlattenFormsFields( children, list, path ) {
  Object.keys( children ).forEach(function (key) {
    var child = children[key];
    if( typeof child.id === 'string' && child.id.charAt(0) === '#' ) {
      // Leave.
      list.push({
        id: child.id,
        caption: child.caption,
        path: path.slice()
      });
    }
    else if( child.children ) {
      // Node.
      path.push( child.caption );
      recursiveFlattenFormsFields( child.children, list, path );
      path.pop();
    }
  });
}
