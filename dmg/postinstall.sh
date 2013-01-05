#!/bin/sh
cd $2
mv "OpenThesaurus Deutsch.dictionary/_OpenThesaurus Deutsch.dictionary" "./._OpenThesaurus Deutsch.dictionary"
/System/Library/CoreServices/FixupResourceForks .
exit 0