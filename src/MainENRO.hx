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


class MainENRO {
	
	inline static var OUTPUT = "Dictionaries/en-ro/Dictionary.xml";
	var cnx :Connection;
	
	public function new () {
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
					database : "ENRO"
				});
		Lib.println ("Connected succesfully!");
		Lib.println ("Start reading the database.");
		
		cnx.request ("SET CHARACTER SET utf8");
		
		var startTime = haxe.Timer.stamp();
		var rset :ResultSet = cnx.request ("SELECT id, cuvant, definitie FROM definitii");
		
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
		var properties = {
			id : "",
			word : "",
			definition : ""
		}
		
		for (row in rows) {
			Reflect.setField (properties, "id", row.id);
			Reflect.setField (properties, "word", row.cuvant);
			Reflect.setField (properties, "definition", row.definitie);
			
			fout.writeString ( definitionTemplate.execute( properties ) + "\n" );
		}
		
		// Write the xml footer
		//fout.writeString ( Resource.getString("backFrontMatter") );
		fout.writeString ( Resource.getString("footer") );
		fout.close();
		
		Lib.println ("Done writing the xml in "+(haxe.Timer.stamp()-startTime)+" seconds");
	}
	
	
	static function main() {
		new MainENRO();
	}
	
}
