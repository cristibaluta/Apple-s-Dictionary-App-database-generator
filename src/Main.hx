#if neko
import neko.Lib;
import neko.db.Mysql;
import neko.db.ResultSet;
import neko.db.Connection;
import neko.io.File;
#elseif php
import php.Lib;
import php.db.Mysql;
import php.db.ResultSet;
import php.db.Connection;
import php.io.File;
#end
import haxe.io.Eof;
import haxe.Template;
import haxe.Resource;


class Main {
	
	inline static var OUTPUT = "Dictionaries/dexoffline/Dictionary.xml";
	var cnx :Connection;
	var sources :Hash<Array<String>>;
	
	public function new () {
		sources = new Hash<Array<String>>();
		readDatabase();
	}
	
	function readDatabase():Void {
		Lib.println ("Connecting to database...");
		cnx = Mysql.connect ({ 
					host : "localhost",
					port : 8889,
					user : "root",
					pass : "root",
					socket : null,
					database : "RODictionary"
				});
		Lib.println ("Connected succesfully!");
		
		cnx.request ("SET CHARACTER SET utf8");
		var sourcesResult :ResultSet = cnx.request ("SELECT id, shortName, name FROM Source");
		for (row in sourcesResult)
			sources.set (Std.string(row.id), [row.name, row.shortName]);
		
		Lib.println ("Start reading the database.");
		var startTime = haxe.Timer.stamp();
		var rset :ResultSet = cnx.request ("SELECT id, sourceId, lexicon, htmlRep FROM Definition WHERE status=0");
		
		Lib.println ("Found "+rset.length+" definitions and took "+(haxe.Timer.stamp()-startTime)+" seconds");
		
		writeXml ( rset );
		
		cnx.close();
	}
	
	function writeXml (rows:ResultSet) :Void {
		
		var startTime = haxe.Timer.stamp();
		Lib.println ("Start writing the xml. This will take some time.");
		
		// open file for writing
		var fout = File.write (OUTPUT, false);
		
		// Write the xml header
		fout.writeString ( Resource.getString("header") );
		
		// Write word template nodes
		var definitionTemplate = new Template ( Resource.getString("definition") );
		var inflectionTemplate = new Template ( Resource.getString("inflections") );
		
		for (row in rows) {
			// Read inflectioned forms
			var lexicon = new Lexicon ( row.lexicon );
			var lexemIdResult = cnx.request ("SELECT lexemId FROM LexemDefinitionMap WHERE definitionId=" + row.id);
			var inflectionsResult = cnx.request ("SELECT DISTINCT formUtf8General FROM InflectedForm WHERE lexemId=" + lexemIdResult.next().lexemId);
/*			var inflectionsResult = cnx.request ("SELECT formUtf8General, InflectedForm.lexemId, LexemDefinitionMap.lexemId, LexemDefinitionMap.definitionId 
													FROM InflectedForm, LexemDefinitionMap 
													WHERE LexemDefinitionMap.definitionId=26124 
													AND LexemDefinitionMap.lexemId=InflectedForm.lexemId");
*/
			for (flection in inflectionsResult)
				lexicon.addInflection ( new Inflection (flection.formUtf8General, "") );
			
			var properties = {
				id : row.id,
				word : row.lexicon,
				definition : row.htmlRep,
				sourceId : Std.string(row.sourceId),
				sourceName : sources.get ( Std.string(row.sourceId) )[0],
				shortSourceName : sources.get ( Std.string(row.sourceId) )[1],
				inflections : inflectionTemplate.execute( lexicon )
			}
			//Lib.println("Inflections: "+Reflect.field(properties, "inflections"));
			fout.writeString ( definitionTemplate.execute( properties ) + "\n" );
			
			lexicon = null;
			lexemIdResult = null;
			inflectionsResult = null;
			properties = null;
		}
		
		// Write the xml footer
		fout.writeString ( Resource.getString("backFrontMatter") );
		fout.writeString ( Resource.getString("footer") );
		fout.close();
		
		Lib.println ("Done writing the xml in "+(haxe.Timer.stamp()-startTime)+" seconds");
	}
	
	
	static function main() {
		new Main();
	}
	
}
