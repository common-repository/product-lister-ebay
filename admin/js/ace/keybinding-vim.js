/* ***** BEGIN LICENSE BLOCK *****
 * Version: MPL 1.1/GPL 2.0/LGPL 2.1
 *
 * The contents of this file are subject to the Mozilla Public License Version
 * 1.1 (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 * The Original Code is Ajax.org Code Editor (ACE).
 *
 * The Initial Developer of the Original Code is
 * Ajax.org B.V.
 * Portions created by the Initial Developer are Copyright (C) 2010
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *      Sergi Mansilla <sergi AT c9 DOT io>
 *      Harutyun Amirjanyan <harutyun AT c9 DOT io>
 *
 * Alternatively, the contents of this file may be used under the terms of
 * either the GNU General Public License Version 2 or later (the "GPL"), or
 * the GNU Lesser General Public License Version 2.1 or later (the "LGPL"),
 * in which case the provisions of the GPL or the LGPL are applicable instead
 * of those above. If you wish to allow use of your version of this file only
 * under the terms of either the GPL or the LGPL, and not to allow others to
 * use your version of this file under the terms of the MPL, indicate your
 * decision by deleting the provisions above and replace them with the notice
 * and other provisions required by the GPL or the LGPL. If you do not delete
 * the provisions above, a recipient may use your version of this file under
 * the terms of any one of the MPL, the GPL or the LGPL.
 *
 * ***** END LICENSE BLOCK ***** */

define(
	'ace/keyboard/vim',
	['require', 'exports', 'module' , 'ace/lib/keys', 'ace/keyboard/vim/commands', 'ace/keyboard/vim/maps/util'],
	function(require, exports, module) {

		var keyUtil      = require( "../lib/keys" );
		var cmds         = require( "./vim/commands" );
		var coreCommands = cmds.coreCommands;
		var util         = require( "./vim/maps/util" );

		var startCommands = {
			"i": {
				command: coreCommands.start
			},
			"I": {
				command: coreCommands.startBeginning
			},
			"a": {
				command: coreCommands.append
			},
			"A": {
				command: coreCommands.appendEnd
			},
			"ctrl-f": {
				command: "gotopagedown"
			},
			"ctrl-b": {
				command: "gotopageup"
			},
		};

		exports.handler = {
			handleKeyboard: function(data, hashId, key, keyCode, e) {
				// ignore command keys (shift, ctrl etc.)
				if (hashId != 0 && (key == "" || key == "\x00")) {
					return null;
				}

				if (hashId == 1) {
					key = "ctrl-" + key;
				}

				if (data.state == "start") {
					if (hashId == -1 || hashId == 1) {
						if (cmds.inputBuffer.idle && startCommands[key]) {
							return startCommands[key];
						}

						return { command: {
							exec: function(editor) {cmds.inputBuffer.push( editor, key );}
							} };
					} // wait for input
					else if (key.length == 1 && (hashId == 0 || hashId == 4)) { // no modifier || shift
						return {command: "null", passEvent: true};
					} else if (key == "esc") {
						return {command: coreCommands.stop};
					}
				} else {
					if (key == "esc" || key == "ctrl-[") {
						data.state = "start";
						return {command: coreCommands.stop};
					} else if (key == "ctrl-w") {
						return {command: "removewordleft"};
					}
				}
			},

			attach: function(editor) {
				editor.on( "click", exports.onCursorMove );
				if (util.currentMode !== "insert") {
					cmds.coreCommands.stop.exec( editor );
				}
			},

			detach: function(editor) {
				editor.removeListener( "click", exports.onCursorMove );
				util.noMode( editor );
				util.currentMode = "normal";
			},

			actions: cmds.actions
		};

		exports.onCursorMove = function(e) {
			cmds.onCursorMove( e.editor, e );
			exports.onCursorMove.scheduled = false;
		};

	}
);

define(
	'ace/keyboard/vim/commands',
	['require', 'exports', 'module' , 'ace/keyboard/vim/maps/util', 'ace/keyboard/vim/maps/motions', 'ace/keyboard/vim/maps/operators', 'ace/keyboard/vim/maps/aliases', 'ace/keyboard/vim/registers'],
	function(require, exports, module) {

		"never use strict";

		var util      = require( "./maps/util" );
		var motions   = require( "./maps/motions" );
		var operators = require( "./maps/operators" );
		var alias     = require( "./maps/aliases" );
		var registers = require( "./registers" );

		var NUMBER   = 1;
		var OPERATOR = 2;
		var MOTION   = 3;
		var ACTION   = 4;
		var HMARGIN  = 8; // Minimum amount of line separation between margins;

		var repeat = function repeat(fn, count, args) {
			while (0 < count--) {
				fn.apply( this, args );
			}
		};

		var ensureScrollMargin = function(editor) {
			var renderer = editor.renderer;
			var pos      = renderer.$cursorLayer.getPixelPosition();

			var top = pos.top;

			var margin = HMARGIN * renderer.layerConfig.lineHeight;
			if (2 * margin > renderer.$size.scrollerHeight) {
				margin = renderer.$size.scrollerHeight / 2;
			}

			if (renderer.scrollTop > top - margin) {
				renderer.session.setScrollTop( top - margin );
			}

			if (renderer.scrollTop + renderer.$size.scrollerHeight < top + margin + renderer.lineHeight) {
				renderer.session.setScrollTop( top + margin + renderer.lineHeight - renderer.$size.scrollerHeight );
			}
		};

		var actions = exports.actions = {
			"z": {
				param: true,
				fn: function(editor, range, count, param) {
					switch (param) {
						case "z":
							editor.alignCursor( null, 0.5 );
							break;
						case "t":
							editor.alignCursor( null, 0 );
							break;
						case "b":
							editor.alignCursor( null, 1 );
							break;
					}
				}
			},
			"r": {
				param: true,
				fn: function(editor, range, count, param) {
					if (param && param.length) {
						repeat( function() { editor.insert( param ); }, count || 1 );
						editor.navigateLeft();
					}
				}
			},
			"R": {
				fn: function(editor, range, count, param) {
					util.insertMode( editor );
					editor.setOverwrite( true );
				}
			},
			"~": {
				fn: function(editor, range, count) {
					repeat(
						function() {
							var range = editor.selection.getRange();
							if (range.isEmpty()) {
								range.end.column++;
							}
							var text    = editor.session.getTextRange( range );
							var toggled = text.toUpperCase();
							if (toggled == text) {
								editor.navigateRight();
							} else {
								editor.session.replace( range, toggled );
							}
						},
						count || 1
					);
				}
			},
			"*": {
				fn: function(editor, range, count, param) {
					editor.selection.selectWord();
					editor.findNext();
					ensureScrollMargin( editor );
					var r = editor.selection.getRange();
					editor.selection.setSelectionRange( r, true );
				}
			},
			"#": {
				fn: function(editor, range, count, param) {
					editor.selection.selectWord();
					editor.findPrevious();
					ensureScrollMargin( editor );
					var r = editor.selection.getRange();
					editor.selection.setSelectionRange( r, true );
				}
			},
			"n": {
				fn: function(editor, range, count, param) {
					var options       = editor.getLastSearchOptions();
					options.backwards = false;

					editor.selection.moveCursorRight();
					editor.selection.clearSelection();
					editor.findNext( options );

					ensureScrollMargin( editor );
					var r        = editor.selection.getRange();
					r.end.row    = r.start.row;
					r.end.column = r.start.column;
					editor.selection.setSelectionRange( r, true );
				}
			},
			"N": {
				fn: function(editor, range, count, param) {
					var options       = editor.getLastSearchOptions();
					options.backwards = true;

					editor.findPrevious( options );
					ensureScrollMargin( editor );
					var r        = editor.selection.getRange();
					r.end.row    = r.start.row;
					r.end.column = r.start.column;
					editor.selection.setSelectionRange( r, true );
				}
			},
			"v": {
				fn: function(editor, range, count, param) {
					editor.selection.selectRight();
					util.visualMode( editor, false );
				},
				acceptsMotion: true
			},
			"V": {
				fn: function(editor, range, count, param) {
					// editor.selection.selectLine();
					// editor.selection.selectLeft();
					var row = editor.getCursorPosition().row;
					editor.selection.clearSelection();
					editor.selection.moveCursorTo( row, 0 );
					editor.selection.selectLineEnd();
					editor.selection.visualLineStart = row;

					util.visualMode( editor, true );
				},
				acceptsMotion: true
			},
			"Y": {
				fn: function(editor, range, count, param) {
					util.copyLine( editor );
				}
			},
			"p": {
				fn: function(editor, range, count, param) {
					var defaultReg = registers._default;

					editor.setOverwrite( false );
					if (defaultReg.isLine) {
						var pos   = editor.getCursorPosition();
						var lines = defaultReg.text.split( "\n" );
						editor.session.getDocument().insertLines( pos.row + 1, lines );
						editor.moveCursorTo( pos.row + 1, 0 );
					} else {
						editor.navigateRight();
						editor.insert( defaultReg.text );
						editor.navigateLeft();
					}
					editor.setOverwrite( true );
					editor.selection.clearSelection();
				}
			},
			"P": {
				fn: function(editor, range, count, param) {
					var defaultReg = registers._default;
					editor.setOverwrite( false );

					if (defaultReg.isLine) {
						var pos   = editor.getCursorPosition();
						var lines = defaultReg.text.split( "\n" );
						editor.session.getDocument().insertLines( pos.row, lines );
						editor.moveCursorTo( pos.row, 0 );
					} else {
						editor.insert( defaultReg.text );
					}
					editor.setOverwrite( true );
					editor.selection.clearSelection();
				}
			},
			"J": {
				fn: function(editor, range, count, param) {
					var session = editor.session;
					range       = editor.getSelectionRange();
					var pos     = {row: range.start.row, column: range.start.column};
					count       = count || range.end.row - range.start.row;
					var maxRow  = Math.min( pos.row + (count || 1), session.getLength() - 1 );

					range.start.column = session.getLine( pos.row ).length;
					range.end.column   = session.getLine( maxRow ).length;
					range.end.row      = maxRow;

					var text = "";
					for (var i = pos.row; i < maxRow; i++) {
						var nextLine = session.getLine( i + 1 );
						text        += " " + / ^ \s * (.*)$ / .exec( nextLine )[1] || "";
					}

					session.replace( range, text );
					editor.moveCursorTo( pos.row, pos.column );
				}
			},
			"u": {
				fn: function(editor, range, count, param) {
					count = parseInt( count || 1, 10 );
					for (var i = 0; i < count; i++) {
						editor.undo();
					}
					editor.selection.clearSelection();
				}
			},
			"ctrl-r": {
				fn: function(editor, range, count, param) {
					count = parseInt( count || 1, 10 );
					for (var i = 0; i < count; i++) {
						editor.redo();
					}
					editor.selection.clearSelection();
				}
			},
			":": {
				fn: function(editor, range, count, param) {
					// not implemented
				}
			},
			"/": {
				fn: function(editor, range, count, param) {
					// not implemented
				}
			},
			"?": {
				fn: function(editor, range, count, param) {
					// not implemented
				}
			},
			".": {
				fn: function(editor, range, count, param) {
					util.onInsertReplaySequence = inputBuffer.lastInsertCommands;
					var previous                = inputBuffer.previous;
					if (previous) { // If there is a previous action
						inputBuffer.exec( editor, previous.action, previous.param );
					}
				}
			}
		};

		var inputBuffer = exports.inputBuffer = {
			accepting: [NUMBER, OPERATOR, MOTION, ACTION],
			currentCmd: null,
			//currentMode: 0,
			currentCount: "",

			// Types
			operator: null,
			motion: null,

			lastInsertCommands: [],

			push: function(editor, char, keyId) {
				this.idle = false;
				var wObj  = this.waitingForParam;
				if (wObj) {
					this.exec( editor, wObj, char );
				}
				// If input is a number (that doesn't start with 0)
				else if ( ! (char === "0" && ! this.currentCount.length) &&
				(char.match( /^\d+$/ ) && this.isAccepting( NUMBER ))) {
					// Assuming that char is always of type String, and not Number
					this.currentCount += char;
					this.currentCmd    = NUMBER;
					this.accepting     = [NUMBER, OPERATOR, MOTION, ACTION];
				} else if ( ! this.operator && this.isAccepting( OPERATOR ) && operators[char]) {
					this.operator   = {
						char: char,
						count: this.getCount()
					};
					this.currentCmd = OPERATOR;
					this.accepting  = [NUMBER, MOTION, ACTION];
					this.exec( editor, { operator: this.operator } );
				} else if (motions[char] && this.isAccepting( MOTION )) {
					this.currentCmd = MOTION;

					var ctx = {
						operator: this.operator,
						motion: {
							char: char,
							count: this.getCount()
						}
					};

					if (motions[char].param) {
						this.waitForParam( ctx );
					} else {
						this.exec( editor, ctx );
					}
				} else if (alias[char] && this.isAccepting( MOTION )) {
					alias[char].operator.count = this.getCount();
					this.exec( editor, alias[char] );
				} else if (actions[char] && this.isAccepting( ACTION )) {
					var actionObj = {
						action: {
							fn: actions[char].fn,
							count: this.getCount()
						}
					};

					if (actions[char].param) {
						this.waitForParam( actionObj );
					} else {
						this.exec( editor, actionObj );
					}

					if (actions[char].acceptsMotion) {
						this.idle = false;
					}
				} else if (this.operator) {
					this.exec( editor, { operator: this.operator }, char );
				} else {
					this.reset();
				}
			},

			waitForParam: function(cmd) {
				this.waitingForParam = cmd;
			},

			getCount: function() {
				var count         = this.currentCount;
				this.currentCount = "";
				return count && parseInt( count, 10 );
			},

			exec: function(editor, action, param) {
				var m = action.motion;
				var o = action.operator;
				var a = action.action;

				if ( ! param) {
					param = action.param;
				}

				if (o) {
					this.previous = {
						action: action,
						param: param
					};
				}

				if (o && ! editor.selection.isEmpty()) {
					if (operators[o.char].selFn) {
						operators[o.char].selFn( editor, editor.getSelectionRange(), o.count, param );
						this.reset();
					}
					return;
				}

				// There is an operator, but no motion or action. We try to pass the
				// current char to the operator to see if it responds to it (an example
				// of this is the 'dd' operator).
				else if ( ! m && ! a && o && param) {
					operators[o.char].fn( editor, null, o.count, param );
					this.reset();
				} else if (m) {
					var run = function(fn) {
						if (fn && typeof fn === "function") { // There should always be a motion
							if (m.count && ! motionObj.handlesCount) {
								repeat( fn, m.count, [editor, null, m.count, param] );
							} else {
								fn( editor, null, m.count, param );
							}
						}
					};

					var motionObj  = motions[m.char];
					var selectable = motionObj.sel;

					if ( ! o) {
						if ((util.onVisualMode || util.onVisualLineMode) && selectable) {
							run( motionObj.sel );
						} else {
							run( motionObj.nav );
						}
					} else if (selectable) {
						repeat(
							function() {
								run( motionObj.sel );
								operators[o.char].fn( editor, editor.getSelectionRange(), o.count, param );
							},
							o.count || 1
						);
					}
					this.reset();
				} else if (a) {
					a.fn( editor, editor.getSelectionRange(), a.count, param );
					this.reset();
				}
				handleCursorMove( editor );
			},

			isAccepting: function(type) {
				return this.accepting.indexOf( type ) !== -1;
			},

			reset: function() {
				this.operator        = null;
				this.motion          = null;
				this.currentCount    = "";
				this.accepting       = [NUMBER, OPERATOR, MOTION, ACTION];
				this.idle            = true;
				this.waitingForParam = null;
			}
		};

		function setPreviousCommand(fn) {
			inputBuffer.previous = { action: { action: { fn: fn } } };
		}

		exports.coreCommands = {
			start: {
				exec: function start(editor) {
					util.insertMode( editor );
					setPreviousCommand( start );
				}
			},
			startBeginning: {
				exec: function startBeginning(editor) {
					editor.navigateLineStart();
					util.insertMode( editor );
					setPreviousCommand( startBeginning );
				}
			},
			// Stop Insert mode as soon as possible. Works like typing <Esc> in
			// insert mode.
			stop: {
				exec: function stop(editor) {
					inputBuffer.reset();
					util.onVisualMode              = false;
					util.onVisualLineMode          = false;
					inputBuffer.lastInsertCommands = util.normalMode( editor );
				}
			},
			append: {
				exec: function append(editor) {
					var pos     = editor.getCursorPosition();
					var lineLen = editor.session.getLine( pos.row ).length;
					if (lineLen) {
						editor.navigateRight();
					}
					util.insertMode( editor );
					setPreviousCommand( append );
				}
			},
			appendEnd: {
				exec: function appendEnd(editor) {
					editor.navigateLineEnd();
					util.insertMode( editor );
					setPreviousCommand( appendEnd );
				}
			}
		};

		var handleCursorMove = exports.onCursorMove = function(editor, e) {
			if (util.currentMode === 'insert' || handleCursorMove.running) {
				return;
			} else if ( ! editor.selection.isEmpty()) {
				handleCursorMove.running = true;
				if (util.onVisualLineMode) {
					var originRow = editor.selection.visualLineStart;
					var cursorRow = editor.getCursorPosition().row;
					if (originRow <= cursorRow) {
						var endLine = editor.session.getLine( cursorRow );
						editor.selection.clearSelection();
						editor.selection.moveCursorTo( originRow, 0 );
						editor.selection.selectTo( cursorRow, endLine.length );
					} else {
						var endLine = editor.session.getLine( originRow );
						editor.selection.clearSelection();
						editor.selection.moveCursorTo( originRow, endLine.length );
						editor.selection.selectTo( cursorRow, 0 );
					}
				}
				handleCursorMove.running = false;
				return;
			} else {
				if (e && (util.onVisualLineMode || util.onVisualMode)) {
					editor.selection.clearSelection();
					util.normalMode( editor );
				}

				handleCursorMove.running = true;
				var pos                  = editor.getCursorPosition();
				var lineLen              = editor.session.getLine( pos.row ).length;

				if (lineLen && pos.column === lineLen) {
					editor.navigateLeft();
				}
				handleCursorMove.running = false;
			}
		};
	}
);
define(
	'ace/keyboard/vim/maps/util',
	['require', 'exports', 'module' , 'ace/keyboard/vim/registers', 'ace/lib/dom'],
	function(require, exports, module) {
		var registers = require( "../registers" );

		var dom = require( "../../../lib/dom" );
		dom.importCssString(
			'.insert-mode. ace_cursor{\
    border-left: 2px solid #333333;\
}\
.ace_dark.insert-mode .ace_cursor{\
    border-left: 2px solid #eeeeee;\
}\
.normal-mode .ace_cursor{\
    border: 0!important;\
    background-color: red;\
    opacity: 0.5;\
}',
			'vimMode'
		);

		module.exports = {
			onVisualMode: false,
			onVisualLineMode: false,
			currentMode: 'normal',
			noMode: function(editor) {
				editor.unsetStyle( 'insert-mode' );
				editor.unsetStyle( 'normal-mode' );
				if (editor.commands.recording) {
					editor.commands.toggleRecording();
				}
				editor.setOverwrite( false );
			},
			insertMode: function(editor) {
				this.currentMode = 'insert';
				// Switch editor to insert mode
				editor.setStyle( 'insert-mode' );
				editor.unsetStyle( 'normal-mode' );

				editor.setOverwrite( false );
				editor.keyBinding.$data.buffer = "";
				editor.keyBinding.$data.state  = "insertMode";
				this.onVisualMode              = false;
				this.onVisualLineMode          = false;
				if (this.onInsertReplaySequence) {
					// Ok, we're apparently replaying ("."), so let's do it
					editor.commands.macro = this.onInsertReplaySequence;
					editor.commands.replay( editor );
					this.onInsertReplaySequence = null;
					this.normalMode( editor );
				} else {
					editor._emit( "vimMode", "insert" );
					// Record any movements, insertions in insert mode
					if ( ! editor.commands.recording) {
						editor.commands.toggleRecording();
					}
				}
			},
			normalMode: function(editor) {
				// Switch editor to normal mode
				this.currentMode = 'normal';

				editor.unsetStyle( 'insert-mode' );
				editor.setStyle( 'normal-mode' );
				editor.clearSelection();

				var pos;
				if ( ! editor.getOverwrite()) {
					pos = editor.getCursorPosition();
					if (pos.column > 0) {
						editor.navigateLeft();
					}
				}

				editor.setOverwrite( true );
				editor.keyBinding.$data.buffer = "";
				editor.keyBinding.$data.state  = "start";
				this.onVisualMode              = false;
				this.onVisualLineMode          = false;
				editor._emit( "changeVimMode", "normal" );
				// Save recorded keystrokes
				if (editor.commands.recording) {
					editor.commands.toggleRecording();
					return editor.commands.macro;
				} else {
					return [];
				}
			},
			visualMode: function(editor, lineMode) {
				if (
				(this.onVisualLineMode && lineMode)
				|| (this.onVisualMode && ! lineMode)
				) {
					this.normalMode( editor );
					return;
				}

				editor.setStyle( 'insert-mode' );
				editor.unsetStyle( 'normal-mode' );

				editor._emit( "changeVimMode", "visual" );
				if (lineMode) {
					this.onVisualLineMode = true;
				} else {
					this.onVisualMode     = true;
					this.onVisualLineMode = false;
				}
			},
			getRightNthChar: function(editor, cursor, char, n) {
				var line    = editor.getSession().getLine( cursor.row );
				var matches = line.substr( cursor.column + 1 ).split( char );

				return n < matches.length ? matches.slice( 0, n ).join( char ).length : null;
			},
			getLeftNthChar: function(editor, cursor, char, n) {
				var line    = editor.getSession().getLine( cursor.row );
				var matches = line.substr( 0, cursor.column ).split( char );

				return n < matches.length ? matches.slice( -1 * n ).join( char ).length : null;
			},
			toRealChar: function(char) {
				if (char.length === 1) {
					return char;
				}

				if (/^shift-./.test( char )) {
					return char[char.length - 1].toUpperCase();
				} else {
					return "";
				}
			},
			copyLine: function(editor) {
				var pos = editor.getCursorPosition();
				editor.selection.clearSelection();
				editor.moveCursorTo( pos.row, pos.column );
				editor.selection.selectLine();
				registers._default.isLine = true;
				registers._default.text   = editor.getCopyText().replace( /\n$/, "" );
				editor.selection.clearSelection();
				editor.moveCursorTo( pos.row, pos.column );
			}
		};
	}
);

define(
	'ace/keyboard/vim/registers',
	['require', 'exports', 'module' ],
	function(require, exports, module) {

		"never use strict";

		module.exports = {
			_default: {
				text: "",
				isLine: false
			}
		};

	}
);

"use strict"

define(
	'ace/keyboard/vim/maps/motions',
	['require', 'exports', 'module' , 'ace/keyboard/vim/maps/util', 'ace/search', 'ace/range'],
	function(require, exports, module) {

		var util = require( "./util" );

		var keepScrollPosition = function(editor, fn) {
			var scrollTopRow = editor.renderer.getScrollTopRow();
			var initialRow   = editor.getCursorPosition().row;
			var diff         = initialRow - scrollTopRow;
			fn && fn.call( editor );
			editor.renderer.scrollToRow( editor.getCursorPosition().row - diff );
		};

		function Motion(getRange, type){
			if (type == 'extend') {
				var extend = true;
			} else {
				var reverse = type;
			}

			this.nav = function(editor) {
				var r = getRange( editor );
				if ( ! r) {
					return;
				}
				if ( ! r.end) {
					var a = r;
				} else if (reverse) {
					var a = r.start;
				} else {
					var a = r.end;
				}

				editor.clearSelection();
				editor.moveCursorTo( a.row, a.column );
			}
			this.sel = function(editor){
				var r = getRange( editor );
				if ( ! r) {
					return;
				}
				if (extend) {
					return editor.selection.setSelectionRange( r );
				}

				if ( ! r.end) {
					var a = r;
				} else if (reverse) {
					var a = r.start;
				} else {
					var a = r.end;
				}

				editor.selection.selectTo( a.row, a.column );
			}
		}

		var nonWordRe       = /[\s.\/\\()\"'-:,.;<>~!@#$%^&*|+=\[\]{}`~?]/;
		var wordSeparatorRe = /[.\/\\()\"'-:,.;<>~!@#$%^&*|+=\[\]{}`~?]/;
		var whiteRe         = /\s/;
		var StringStream    = function(editor, cursor) {
			var sel           = editor.selection;
			this.range        = sel.getRange();
			cursor            = cursor || sel.selectionLead;
			this.row          = cursor.row;
			this.col          = cursor.column;
			var line          = editor.session.getLine( this.row );
			var maxRow        = editor.session.getLength()
			this.ch           = line[this.col] || '\n'
			this.skippedLines = 0;

			this.next = function() {
				this.ch = line[++this.col] || this.handleNewLine( 1 );
				// this.debug()
				return this.ch;
			}
			this.prev = function() {
				this.ch = line[--this.col] || this.handleNewLine( -1 );
				// this.debug()
				return this.ch;
			}
			this.peek = function(dir) {
				var ch = line[this.col + dir];
				if (ch) {
					return ch;
				}
				if (dir == -1) {
					return '\n';
				}
				if (this.col == line.length - 1) {
					return '\n';
				}
				return editor.session.getLine( this.row + 1 )[0] || '\n';
			}

			this.handleNewLine = function(dir) {
				if (dir == 1) {
					if (this.col == line.length) {
						return '\n';
					}
					if (this.row == maxRow - 1) {
						return '';
					}
					this.col = 0;
					this.row ++;
					line = editor.session.getLine( this.row );
					this.skippedLines++;
					return line[0] || '\n';
				}
				if (dir == -1) {
					if (this.row == 0) {
						return '';
					}
					this.row --;
					line     = editor.session.getLine( this.row );
					this.col = line.length;
					this.skippedLines--;
					return '\n';
				}
			}
			this.debug         = function() {
				console.log( line.substring( 0, this.col ) + '|' + this.ch + '\'' + this.col + '\'' + line.substr( this.col + 1 ) );
			}
		}

		var Search = require( "ace/search" ).Search;
		var search = new Search();

		function find(editor, needle, dir) {
			search.$options.needle    = needle;
			search.$options.backwards = dir == -1;
			return search.find( editor.session );
		}

		var Range = require( "ace/range" ).Range;

		module.exports = {
			"w": new Motion(
				function(editor) {
					var str = new StringStream( editor );

					if (str.ch && wordSeparatorRe.test( str.ch )) {
						while (str.ch && wordSeparatorRe.test( str.ch )) {
							str.next();
						}
					} else {
						while (str.ch && ! nonWordRe.test( str.ch )) {
							str.next();
						}
					}
					while (str.ch && whiteRe.test( str.ch ) && str.skippedLines < 2) {
						str.next();
					}

					str.skippedLines == 2 && str.prev();
					return {column: str.col, row: str.row};
				}
			),
		"W": new Motion(
			function(editor) {
				var str = new StringStream( editor );
				while (str.ch && ! (whiteRe.test( str.ch ) && ! whiteRe.test( str.peek( 1 ) )) && str.skippedLines < 2) {
					str.next();
				}
				if (str.skippedLines == 2) {
					str.prev();
				} else {
					str.next();
				}

				return {column: str.col, row: str.row}
			}
		),
		"b": new Motion(
			function(editor) {
				var str = new StringStream( editor );

				str.prev();
				while (str.ch && whiteRe.test( str.ch ) && str.skippedLines > -2) {
					str.prev();
				}

				if (str.ch && wordSeparatorRe.test( str.ch )) {
					while (str.ch && wordSeparatorRe.test( str.ch )) {
						str.prev();
					}
				} else {
					while (str.ch && ! nonWordRe.test( str.ch )) {
						str.prev();
					}
				}
				str.ch && str.next();
				return {column: str.col, row: str.row};
			}
		),
		"B": new Motion(
			function(editor) {
				var str = new StringStream( editor )
				str.prev();
				while (str.ch && ! ( ! whiteRe.test( str.ch ) && whiteRe.test( str.peek( -1 ) )) && str.skippedLines > -2) {
					str.prev();
				}

				if (str.skippedLines == -2) {
					str.next();
				}

				return {column: str.col, row: str.row};
			},
			true
		),
		"e": new Motion(
			function(editor) {
				var str = new StringStream( editor );

				str.next();
				while (str.ch && whiteRe.test( str.ch )) {
					str.next();
				}

				if (str.ch && wordSeparatorRe.test( str.ch )) {
					while (str.ch && wordSeparatorRe.test( str.ch )) {
						str.next();
					}
				} else {
					while (str.ch && ! nonWordRe.test( str.ch )) {
						str.next();
					}
				}
				str.ch && str.prev();
				return {column: str.col, row: str.row};
			}
		),
		"E": new Motion(
			function(editor) {
				var str = new StringStream( editor );
				str.next();
				while (str.ch && ! ( ! whiteRe.test( str.ch ) && whiteRe.test( str.peek( 1 ) ))) {
					str.next();
				}

				return {column: str.col, row: str.row};
			}
		),

		"l": {
			nav: function(editor) {
				editor.navigateRight();
			},
			sel: function(editor) {
				var pos     = editor.getCursorPosition();
				var col     = pos.column;
				var lineLen = editor.session.getLine( pos.row ).length;

				// Solving the behavior at the end of the line due to the
				// different 0 index-based colum positions in ACE.
				if (lineLen && col !== lineLen) { // In selection mode you can select the newline
					editor.selection.selectRight();
				}
			}
			},
			"h": {
				nav: function(editor) {
					var pos = editor.getCursorPosition();
					if (pos.column > 0) {
						editor.navigateLeft();
					}
				},
				sel: function(editor) {
					var pos = editor.getCursorPosition();
					if (pos.column > 0) {
						editor.selection.selectLeft();
					}
				}
			},
			"k": {
				nav: function(editor) {
					editor.navigateUp();
				},
				sel: function(editor) {
					editor.selection.selectUp();
				}
			},
			"j": {
				nav: function(editor) {
					editor.navigateDown();
				},
				sel: function(editor) {
					editor.selection.selectDown();
				}
			},

			"i": {
				param: true,
				sel: function(editor, range, count, param) {
					switch (param) {
						case "w":
							editor.selection.selectWord();
							break;
						case "W":
							editor.selection.selectAWord();
							break;
						case "(":
						case "{":
						case "[":
							var cursor = editor.getCursorPosition()
							var end    = editor.session.$findClosingBracket( param, cursor, /paren/ )
							if ( ! end) {
								return;
							}
							var start = editor.session.$findOpeningBracket( editor.session.$brackets[param], cursor, /paren/ )
							if ( ! start) {
								return;
							}
							start.column ++;
							editor.selection.setSelectionRange( Range.fromPoints( start, end ) )
							break
						case "'":
						case "\"":
						case "/":
							var end = find( editor, param, 1 )
							if ( ! end) {
								return;
							}
							var start = find( editor, param, -1 )
							if ( ! start) {
								return;
							}
							editor.selection.setSelectionRange( Range.fromPoints( start.end, end.start ) )
							break
					}
				}
			},
			"a": {
				param: true,
				sel: function(editor, range, count, param) {
					switch (param) {
						case "w":
							editor.selection.selectAWord();
							break;
						case "W":
							editor.selection.selectAWord();
							break;
						case "(":
						case "{":
						case "[":
							var cursor = editor.getCursorPosition();
							var end    = editor.session.$findClosingBracket( param, cursor, /paren/ );
							if ( ! end) {
								return;
							}
							var start = editor.session.$findOpeningBracket( editor.session.$brackets[param], cursor, /paren/ );
							if ( ! start) {
								return;
							}
							end.column ++;
							editor.selection.setSelectionRange( Range.fromPoints( start, end ) );
							break;
						case "'":
						case "\"":
						case "/":
							var end = find( editor, param, 1 );
							if ( ! end) {
								return;
							}
							var start = find( editor, param, -1 );
							if ( ! start) {
								return;
							}
							end.column ++;
							editor.selection.setSelectionRange( Range.fromPoints( start.start, end.end ) );
							break;
					}
				}
			},

			"f": {
				param: true,
				handlesCount: true,
				nav: function(editor, range, count, param) {
					var ed     = editor;
					var cursor = ed.getCursorPosition();
					var column = util.getRightNthChar( editor, cursor, param, count || 1 );

					if (typeof column === "number") {
						ed.selection.clearSelection(); // Why does it select in the first place?
						ed.moveCursorTo( cursor.row, column + cursor.column + 1 );
					}
				},
				sel: function(editor, range, count, param) {
					var ed     = editor;
					var cursor = ed.getCursorPosition();
					var column = util.getRightNthChar( editor, cursor, param, count || 1 );

					if (typeof column === "number") {
						ed.moveCursorTo( cursor.row, column + cursor.column + 1 );
					}
				}
			},
			"F": {
				param: true,
				handlesCount: true,
				nav: function(editor, range, count, param) {
					count      = parseInt( count, 10 ) || 1;
					var ed     = editor;
					var cursor = ed.getCursorPosition();
					var column = util.getLeftNthChar( editor, cursor, param, count );

					if (typeof column === "number") {
						ed.selection.clearSelection(); // Why does it select in the first place?
						ed.moveCursorTo( cursor.row, cursor.column - column - 1 );
					}
				},
				sel: function(editor, range, count, param) {
					var ed     = editor;
					var cursor = ed.getCursorPosition();
					var column = util.getLeftNthChar( editor, cursor, param, count || 1 );

					if (typeof column === "number") {
						ed.moveCursorTo( cursor.row, cursor.column - column - 1 );
					}
				}
			},
			"t": {
				param: true,
				handlesCount: true,
				nav: function(editor, range, count, param) {
					var ed     = editor;
					var cursor = ed.getCursorPosition();
					var column = util.getRightNthChar( editor, cursor, param, count || 1 );

					if (typeof column === "number") {
						ed.selection.clearSelection(); // Why does it select in the first place?
						ed.moveCursorTo( cursor.row, column + cursor.column );
					}
				},
				sel: function(editor, range, count, param) {
					var ed     = editor;
					var cursor = ed.getCursorPosition();
					var column = util.getRightNthChar( editor, cursor, param, count || 1 );

					if (typeof column === "number") {
						ed.moveCursorTo( cursor.row, column + cursor.column );
					}
				}
			},
			"T": {
				param: true,
				handlesCount: true,
				nav: function(editor, range, count, param) {
					var ed     = editor;
					var cursor = ed.getCursorPosition();
					var column = util.getLeftNthChar( editor, cursor, param, count || 1 );

					if (typeof column === "number") {
						ed.selection.clearSelection(); // Why does it select in the first place?
						ed.moveCursorTo( cursor.row, -column + cursor.column );
					}
				},
				sel: function(editor, range, count, param) {
					var ed     = editor;
					var cursor = ed.getCursorPosition();
					var column = util.getLeftNthChar( editor, cursor, param, count || 1 );

					if (typeof column === "number") {
						ed.moveCursorTo( cursor.row, -column + cursor.column );
					}
				}
			},

			"^": {
				nav: function(editor) {
					editor.navigateLineStart();
				},
				sel: function(editor) {
					editor.selection.selectLineStart();
				}
			},
			"$": {
				nav: function(editor) {
					editor.navigateLineEnd();
				},
				sel: function(editor) {
					editor.selection.selectLineEnd();
				}
			},
			"0": {
				nav: function(editor) {
					var ed = editor;
					ed.navigateTo( ed.selection.selectionLead.row, 0 );
				},
				sel: function(editor) {
					var ed = editor;
					ed.selectTo( ed.selection.selectionLead.row, 0 );
				}
			},
			"G": {
				nav: function(editor, range, count, param) {
					if ( ! count && count !== 0) { // Stupid JS
						count = editor.session.getLength();
					}
					editor.gotoLine( count );
				},
				sel: function(editor, range, count, param) {
					if ( ! count && count !== 0) { // Stupid JS
						count = editor.session.getLength();
					}
					editor.selection.selectTo( count, 0 );
				}
			},
			"g": {
				param: true,
				nav: function(editor, range, count, param) {
					switch (param) {
						case "m":
							console.log( "Middle line" );
							break;
						case "e":
							console.log( "End of prev word" );
							break;
						case "g":
							editor.gotoLine( count || 0 );
						case "u":
							editor.gotoLine( count || 0 );
						case "U":
							editor.gotoLine( count || 0 );
					}
				},
				sel: function(editor, range, count, param) {
					switch (param) {
						case "m":
							console.log( "Middle line" );
							break;
						case "e":
							console.log( "End of prev word" );
							break;
						case "g":
							editor.selection.selectTo( count || 0, 0 );
					}
				}
			},
			"o": {
				nav: function(editor, range, count, param) {
					count       = count || 1;
					var content = "";
					while (0 < count--) {
						content += "\n";
					}

					if (content.length) {
						editor.navigateLineEnd()
						editor.insert( content );
						util.insertMode( editor );
					}
				}
			},
			"O": {
				nav: function(editor, range, count, param) {
					var row     = editor.getCursorPosition().row;
					count       = count || 1;
					var content = "";
					while (0 < count--) {
						content += "\n";
					}

					if (content.length) {
						if (row > 0) {
							editor.navigateUp();
							editor.navigateLineEnd()
							editor.insert( content );
						} else {
							editor.session.insert( {row: 0, column: 0}, content );
							editor.navigateUp();
						}
						util.insertMode( editor );
					}
				}
			},
			"%": new Motion(
				function(editor){
					var brRe   = /[\[\]{}()]/g;
					var cursor = editor.getCursorPosition();
					var ch     = editor.session.getLine( cursor.row )[cursor.column];
					if ( ! brRe.test( ch )) {
						var range = find( editor, brRe );
						if ( ! range) {
							return;
						}
						cursor = range.start;
					}
					var match = editor.session.findMatchingBracket(
						{
							row: cursor.row,
							column: cursor.column + 1
						}
					);

					return match;
				}
			),
		"ctrl-d": {
			nav: function(editor, range, count, param) {
				editor.selection.clearSelection();
				keepScrollPosition( editor, editor.gotoPageDown );
			},
			sel: function(editor, range, count, param) {
				keepScrollPosition( editor, editor.selectPageDown );
			}
			},
			"ctrl-u": {
				nav: function(editor, range, count, param) {
					editor.selection.clearSelection();
					keepScrollPosition( editor, editor.gotoPageUp );

				},
				sel: function(editor, range, count, param) {
					keepScrollPosition( editor, editor.selectPageUp );
				}
			},
		};

		module.exports.backspace = module.exports.left = module.exports.h;
		module.exports.right     = module.exports.l;
		module.exports.up        = module.exports.k;
		module.exports.down      = module.exports.j;
		module.exports.pagedown  = module.exports["ctrl-d"];
		module.exports.pageup    = module.exports["ctrl-u"];

	}
);

define(
	'ace/keyboard/vim/maps/operators',
	['require', 'exports', 'module' , 'ace/keyboard/vim/maps/util', 'ace/keyboard/vim/registers'],
	function(require, exports, module) {

		"never use strict";

		var util      = require( "./util" );
		var registers = require( "../registers" );

		module.exports = {
			"d": {
				selFn: function(editor, range, count, param) {
					registers._default.text   = editor.getCopyText();
					registers._default.isLine = util.onVisualLineMode;
					if (util.onVisualLineMode) {
						editor.removeLines();
					} else {
						editor.session.remove( range );
					}
					util.normalMode( editor );
				},
				fn: function(editor, range, count, param) {
					count = count || 1;
					switch (param) {
						case "d":
							registers._default.text   = "";
							registers._default.isLine = true;
							for (var i = 0; i < count; i++) {
								editor.selection.selectLine();
								registers._default.text += editor.getCopyText();
								var selRange             = editor.getSelectionRange();
								editor.session.remove( selRange );
								editor.selection.clearSelection();
							}
							registers._default.text = registers._default.text.replace( /\n$/, "" );
							break;
						default:
							if (range) {
								editor.selection.setSelectionRange( range );
								registers._default.text   = editor.getCopyText();
								registers._default.isLine = false;
								editor.session.remove( range );
								editor.selection.clearSelection();
							}
					}
				}
			},
			"c": {
				selFn: function(editor, range, count, param) {
					editor.session.remove( range );
					util.insertMode( editor );
				},
				fn: function(editor, range, count, param) {
					count = count || 1;
					switch (param) {
						case "c":
							for (var i = 0; i < count; i++) {
								editor.removeLines();
								util.insertMode( editor );
							}

							break;
						default:
							if (range) {

								// range.end.column ++;
								editor.session.remove( range );
								util.insertMode( editor );
							}
					}
				}
			},
			"y": {
				selFn: function(editor, range, count, param) {
					registers._default.text   = editor.getCopyText();
					registers._default.isLine = util.onVisualLineMode;
					editor.selection.clearSelection();
					util.normalMode( editor );
				},
				fn: function(editor, range, count, param) {
					count = count || 1;
					switch (param) {
						case "y":
							var pos = editor.getCursorPosition();
							editor.selection.selectLine();
							for (var i = 0; i < count - 1; i++) {
								editor.selection.moveCursorDown();
							}
							registers._default.text = editor.getCopyText().replace( /\n$/, "" );
							editor.selection.clearSelection();
							registers._default.isLine = true;
							editor.moveCursorToPosition( pos );
							break;
						default:
							if (range) {
								var pos = editor.getCursorPosition();
								editor.selection.setSelectionRange( range );
								registers._default.text   = editor.getCopyText();
								registers._default.isLine = false;
								editor.selection.clearSelection();
								editor.moveCursorTo( pos.row, pos.column );
							}
					}
				}
			},
			">": {
				selFn: function(editor, range, count, param) {
					count = count || 1;
					for (var i = 0; i < count; i++) {
						editor.indent();
					}
					util.normalMode( editor );
				},
				fn: function(editor, range, count, param) {
					count = parseInt( count || 1, 10 );
					switch (param) {
						case ">":
							var pos = editor.getCursorPosition();
							editor.selection.selectLine();
							for (var i = 0; i < count - 1; i++) {
								editor.selection.moveCursorDown();
							}
							editor.indent();
							editor.selection.clearSelection();
							editor.moveCursorToPosition( pos );
							editor.navigateLineEnd();
							editor.navigateLineStart();
							break;
					}
				}
			},
			"<": {
				selFn: function(editor, range, count, param) {
					count = count || 1;
					for (var i = 0; i < count; i++) {
						editor.blockOutdent();
					}
					util.normalMode( editor );
				},
				fn: function(editor, range, count, param) {
					count = count || 1;
					switch (param) {
						case "<":
							var pos = editor.getCursorPosition();
							editor.selection.selectLine();
							for (var i = 0; i < count - 1; i++) {
								editor.selection.moveCursorDown();
							}
							editor.blockOutdent();
							editor.selection.clearSelection();
							editor.moveCursorToPosition( pos );
							editor.navigateLineEnd();
							editor.navigateLineStart();
							break;
					}
				}
			}
		};
	}
);

"use strict"

define(
	'ace/keyboard/vim/maps/aliases',
	['require', 'exports', 'module' ],
	function(require, exports, module) {
		module.exports = {
			"x": {
				operator: {
					char: "d",
					count: 1
				},
				motion: {
					char: "l",
					count: 1
				}
			},
			"X": {
				operator: {
					char: "d",
					count: 1
				},
				motion: {
					char: "h",
					count: 1
				}
			},
			"D": {
				operator: {
					char: "d",
					count: 1
				},
				motion: {
					char: "$",
					count: 1
				}
			},
			"C": {
				operator: {
					char: "c",
					count: 1
				},
				motion: {
					char: "$",
					count: 1
				}
			},
			"s": {
				operator: {
					char: "c",
					count: 1
				},
				motion: {
					char: "l",
					count: 1
				}
			},
			"S": {
				operator: {
					char: "c",
					count: 1
				},
				param: "c"
			}
		};
	}
);
