import neko.Lib;
import neko.io.File;
import haxe.io.Eof;
import haxe.Template;
import haxe.Resource;


class MainHello {
	
	inline static var INPUT = "../iPhone/Hello/source";
	inline static var OUTPUT = "../iPhone/Hello/hellos.plist";
	inline static var SQL_OUTPUT = "../iPhone/Hello/hellos.sql";
	//inline static var OUTPUT_SAMPLES = "../iPhone/Hello/samples.plist";
	
	public function new () {
		Lib.println ("Start reading the txt file.");
		var startTime = haxe.Timer.stamp();
		
		var fin = File.read (INPUT, false);
		var fout = File.write (OUTPUT, false);
			fout.writeString ( Resource.getString("header") );
		var sql_out = File.write (SQL_OUTPUT, false);
			sql_out.writeString ( Resource.getString("sql_header") );
		var letters = 0, id=0;
		var newLetter = true;
		var letterTemplate = new Template ( Resource.getString("letter") );
		var helloTemplate = new Template ( Resource.getString("hello") );
		var sqlRecordTemplate = new Template ( Resource.getString("sql_record") );
		var properties = {
			language : "",
			region : "",
			usage : "",
			hello : "",
			hasSample : "0",
			id : "0"
		}
		var arr1 = new Array<String>();
		var arr2 = new Array<String>();
		var chars = ["&", "'"];
		var replacements = ["&amp;", "&apos;"];
		
		try {
		while ( true ) {
			var str = fin.readLine();
			
			if (str == "") {
				letters ++;
				newLetter = true;// Skip this line but mark the next line as starting with a new letter
				continue;
			}
			
			
			arr1 = str.split("(");
			arr2 = str.split("[");
			
			properties = {
				language : StringTools.rtrim ( ((str.indexOf("(") > str.indexOf("[") && str.indexOf("[") != -1) || (str.indexOf("[") != -1 && str.indexOf("(") == -1)) ? arr2[0] : arr1[0] ),
				region : arr1.length > 1 ? arr1.pop().split(")").shift() : "",
				usage : arr2.length > 1 ? arr2.pop().split("]").shift() : "",
				hello : StringTools.ltrim ( str.substr(45) ),
				id : Std.string(id)
			}
			
			for (key in Reflect.fields(properties)) for (i in 0...chars.length)
				Reflect.setField (properties, key, StringTools.replace(Reflect.field(properties,key), chars[i], replacements[i]));
			
			if (newLetter) {
				newLetter = false;
				// Find the new letter
				var letter = arr1[0].substr(0, 1).toLowerCase();
				Lib.println ("new letter "+letter);
				
				if (letters > 0)
					fout.writeString ("	</array>\n" );// close the last letter
					fout.writeString ( letterTemplate.execute( {letter : letter} ) + "\n" );
					fout.writeString ( helloTemplate.execute( properties ) + "\n" );
					sql_out.writeString ( sqlRecordTemplate.execute( properties ) + "\n" );
				letters ++;
			}
			else {
				fout.writeString ( helloTemplate.execute( properties ) + "\n" );
				sql_out.writeString ( sqlRecordTemplate.execute( properties ) + "\n" );
			}
			id++;
		}
		} catch (ex:Eof) {}
		
		fout.writeString ("	</array>\n" );
		fout.writeString ( Resource.getString("footer") );
		sql_out.writeString ("COMMIT;");
		fout.close();
		fin.close();
		sql_out.close();
		
		Lib.println ("Finished and took "+(haxe.Timer.stamp()-startTime)+" seconds");
	}
	
	static function main () {
		new MainHello();
	}
}
