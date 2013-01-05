import neko.Lib;
import neko.io.File;
import haxe.io.Eof;
import haxe.Template;
import haxe.Resource;


class MainStarDict {
	
	inline static var INPUT = "Dictionary Development Kit/en-ro-en/dict.xdxf";
	inline static var OUTPUT = "Dictionary Development Kit/en-ro-en/en-ro.xml";
	
	public function new () {
		Lib.println ("Start reading the stardict database.");
		var startTime = haxe.Timer.stamp();
		
		// open and read file line by line
		var in_out = neko.Sys.args();
		if (in_out.length < 2) {
			Lib.println ("Invalid parameters. The command is: neko StardictToXml.n input.xdxf output.xml");
			return;
		}
		var fin = File.read (in_out[0], false);
		var fout = File.write (in_out[1], false);
			fout.writeString ( Resource.getString("header") );
		var lineNum = 0;
		var englishWord = true;
		var wordTemplate = new Template ( Resource.getString("word") );
		var properties = {
			id : "",
			word_en : "",
			word_ro : "",
			bidirectional : false
		}
		
		try {
		while ( true ) {
			var str = fin.readLine();
			//Lib.println (str+" startsWith <ar><k>: "+StringTools.startsWith (str, "<ar><k>"));
			if (StringTools.startsWith (str, "<ar><k>")) {
				englishWord = true;
				var word = str.substr(7, str.length-4-7);
				Reflect.setField (properties, "id", word);
				Reflect.setField (properties, "word_en", word);
			}
			else if (StringTools.endsWith (str, "</ar>"))	{
				englishWord = false;
				Reflect.setField (properties, "word_ro", str.substr(0, -5));
			}
			
			if (!englishWord) {
				lineNum ++;
				fout.writeString ( wordTemplate.execute( properties ) + "\n" );
			}
		}
		} catch (ex:Eof) {}
		
		fout.writeString ( Resource.getString("footer") );
		fout.close();
		fin.close();
		
		Lib.println ("Found "+lineNum+" definitions and took "+(haxe.Timer.stamp()-startTime)+" seconds");
	}
	
	static function main () {
		new MainStarDict();
	}
}
