/**
 *  <d:index d:value="making"/>
 *	<d:index d:value="made" d:title="made (make)"/>
 *	<d:index d:value="make it" d:parental-control="1" d:anchor="xpointer(//*[@id='::word_id::'])"/>
 */

// Table identifiers: id, sourceId, lexicon, internalRep, htmlRep


class Inflection {
	
	var value :String;
	var title :String;
	//var parentalControl :String;
	//var anchor :String;
	
	
	public function new (value, title) {
		this.value = value;
		this.title = title;
	}
}
