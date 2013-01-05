class Lexicon {
	
	var name :String;
	var inflections :List<Inflection>;
	
	
	public function new ( name ) {
		this.name = name;
		this.inflections = new List();
	}
	
	public function addInflection (w:Inflection) :Void {
		inflections.add ( w );
	}
}
