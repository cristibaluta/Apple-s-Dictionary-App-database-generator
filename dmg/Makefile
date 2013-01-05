#
# Makefile
#
#
#

###########################

# You need to edit these values.

DICT_NAME       = OpenThesaurus Deutsch
DICT_SRC_PATH   = ThesaurusDeutsch.xml
CSS_PATH        = ThesaurusDeutsch.css
PMDOC_NAME      = ThesaurusDeutsch.pmdoc
PLIST_PATH      = Info.plist
DATE            = `date +"v%Y.%m.%d"`

DICT_BUILD_OPTS = -c 1 -e 0
# Suppress adding supplementary key.
# DICT_BUILD_OPTS		=	-s 0	# Suppress adding supplementary key.

###########################

# The DICT_BUILD_TOOL_DIR value is used also in "build_dict.sh" script.
# You need to set it when you invoke the script directly.

DICT_BUILD_TOOL_DIR	=	"/Developer/Extras/Dictionary Development Kit"
DICT_BUILD_TOOL_BIN	=	"$(DICT_BUILD_TOOL_DIR)/bin"

###########################

DICT_DEV_KIT_OBJ_DIR	=	./objects
export	DICT_DEV_KIT_OBJ_DIR

DESTINATION_FOLDER	=	/Library/Dictionaries
RM			=	/bin/rm

CR = `echo "\r"`

###########################

all:
	@/usr/bin/python createxml.py
	@"$(DICT_BUILD_TOOL_BIN)/build_dict.sh" $(DICT_BUILD_OPTS) "$(DICT_NAME)" $(DICT_SRC_PATH) $(CSS_PATH) $(PLIST_PATH)
	@cp PkgInfo "$(DICT_DEV_KIT_OBJ_DIR)/$(DICT_NAME).dictionary/Contents"
	@cp dictplug.icns "$(DICT_DEV_KIT_OBJ_DIR)/$(DICT_NAME).dictionary/Contents/Resources"
#	@SetFile -a e "$(DICT_DEV_KIT_OBJ_DIR)/$(DICT_NAME).dictionary"
	@echo "Done."
	@echo "Use 'sudo make install' to install the dictionary or 'make package' to create the Install Package."

package:
	@echo "- Creating install package."
	@hdiutil create -type SPARSE -ov -fs HFS+ -attach -noscrub -layout NONE "DMG.sparseimage" -volname "$(DICT_NAME)"
	@mkdir -p "/Volumes/$(DICT_NAME)/.bg"
	@cp dmg.background.png "/Volumes/$(DICT_NAME)/.bg/background.png"
	@cp dmg.DS_Store "/Volumes/$(DICT_NAME)/.DS_Store"
	@cp dmg.VolumeIcon.icns "/Volumes/$(DICT_NAME)/.VolumeIcon.icns"
	@SetFile -a C "/Volumes/$(DICT_NAME)/"
	@/Developer/Applications/Utilities/PackageMaker.app/Contents/MacOS/PackageMaker -d $(PMDOC_NAME) -i de.tekl.dictionary.openThesaurusDeutsch.pkg -o "/Volumes/$(DICT_NAME)/$(DICT_NAME) $(DATE).pkg" --filter '/\.svn$$'
	@osascript -e 'tell application "Finder" to open "$(DICT_NAME):"'
	@osascript -e 'tell application "Finder" to set background picture of (icon view options of Finder window 1) to "$(DICT_NAME):.bg:background.png"'
	@osascript -e 'tell application "Finder" to set toolbar visible of Finder window 1 to false'
	@osascript -e 'tell application "Finder" to set bounds of Finder window 1 to {100, 100, 660, 470}'
	@osascript -e 'tell application "Finder" to set arrangement of icon view options of Finder window 1 to not arranged'
	@osascript -e 'tell application "Finder" to set current view of Finder window 1 to icon view'
	@osascript -e 'tell application "Finder" to set icon size of icon view options of Finder window 1 to 128'
	@osascript -e 'tell application "Finder" to set position of item 1 of Finder window 1 to {131, 159}'
	@osascript -e 'tell application "Finder" to close Finder window 1'
	@osascript -e 'tell application "Finder" to delay 3'
	@osascript -e 'tell application "Finder" to open "$(DICT_NAME):"'
	@osascript -e 'tell application "Finder" to close Finder window 1'
	@osascript -e 'tell application "Finder" to delay 3'
	@echo "- Finishing internet-enabled disk image."
	@hdiutil eject "/Volumes/$(DICT_NAME)"
	@hdiutil convert -ov -format UDBZ DMG.sparseimage -o "$(DICT_NAME) $(DATE).dmg"
	@hdiutil internet-enable "$(DICT_NAME) $(DATE).dmg"
	@rm DMG.sparseimage
	@echo "Done."

delivery:
	@echo "- Creating delivery package."
	@hdiutil create -type SPARSE -ov -fs HFS+ -attach -noscrub -layout NONE "DMG.sparseimage" -volname "$(DICT_NAME) $(DATE)"
	@mkdir -p "/Volumes/$(DICT_NAME) $(DATE)/.bg"
	@find "$(DICT_DEV_KIT_OBJ_DIR)/$(DICT_NAME).dictionary" -name .svn -exec rm -rf {} \;
	@cp -R "$(DICT_DEV_KIT_OBJ_DIR)/$(DICT_NAME).dictionary" "/Volumes/$(DICT_NAME) $(DATE)"
#	@sudo chown -R 0:80 "/Volumes/$(DICT_NAME) $(DATE)/$(DICT_NAME).dictionary"
#	@sudo chmod -R ug+rwx "/Volumes/$(DICT_NAME) $(DATE)/$(DICT_NAME).dictionary"
	@cp -R OtherResources/Readme.rtfd "/Volumes/$(DICT_NAME) $(DATE)"
	@cp -R OtherResources/Liesmich.rtfd "/Volumes/$(DICT_NAME) $(DATE)"
	@cp dmg.VolumeIcon.icns "/Volumes/$(DICT_NAME) $(DATE)/.VolumeIcon.icns"
	@SetFile -a C "/Volumes/$(DICT_NAME) $(DATE)/"
	@osascript -e 'tell application "Finder" to make new alias file at ("$(DICT_NAME) " & do shell script "date +v%Y.%m.%d") to POSIX file "/Library/Dictionaries"'
	@cp dmg.background2.png "/Volumes/$(DICT_NAME) $(DATE)/.bg/background.png"
	@cp dmg.DS_Store "/Volumes/$(DICT_NAME) $(DATE)/.DS_Store"
	@osascript -e 'tell application "Finder" to open "$(DICT_NAME) " & do shell script "date +v%Y.%m.%d" & ":"'
	@osascript -e 'tell application "Finder" to set background picture of (icon view options of window 1) to "$(DICT_NAME) " & do shell script "date +v%Y.%m.%d" & ":.bg:background.png"'
	@osascript -e 'tell application "Finder" to set toolbar visible of Finder window 1 to false'
	@osascript -e 'tell application "Finder" to set bounds of Finder window 1 to {100, 100, 660, 470}'
	@osascript -e 'tell application "Finder" to set arrangement of icon view options of Finder window 1 to not arranged'
	@osascript -e 'tell application "Finder" to set current view of Finder window 1 to icon view'
	@osascript -e 'tell application "Finder" to set icon size of icon view options of Finder window 1 to 64'
	@osascript -e 'tell application "Finder" to set position of item 3 of Finder window 1 to {188, 104}'
	@osascript -e 'tell application "Finder" to set position of item 1 of Finder window 1 to {368, 104}'
	@osascript -e 'tell application "Finder" to set position of item 2 of Finder window 1 to {218, 253}'
	@osascript -e 'tell application "Finder" to set position of item 4 of Finder window 1 to {478, 253}'
	@osascript -e 'tell application "Finder" to close Finder window 1'
	@osascript -e 'tell application "Finder" to delay 3'
	@osascript -e 'tell application "Finder" to open "$(DICT_NAME) " & do shell script "date +v%Y.%m.%d" & ":"'
	@osascript -e 'tell application "Finder" to close Finder window 1'
	@osascript -e 'tell application "Finder" to delay 3'
	@echo "- Finishing disk image."
	@hdiutil eject "/Volumes/$(DICT_NAME) $(DATE)"
	@hdiutil convert -ov -format UDBZ DMG.sparseimage -o "$(DICT_NAME) $(DATE).dmg"
	@rm DMG.sparseimage
	@echo "Done."
	
dmg:
	@echo "- Creating delivery image."
	@hdiutil create -type SPARSE -ov -fs HFS+ -attach -noscrub -layout NONE "DMG.sparseimage" -volname "$(DICT_NAME) $(DATE)"
	@mkdir -p "/Volumes/$(DICT_NAME) $(DATE)/.bg"
	@find "$(DICT_DEV_KIT_OBJ_DIR)/$(DICT_NAME).dictionary" -name "*.svn*" -exec rm -rf {} \; ; exit 0
	@cp -R -p "$(DICT_DEV_KIT_OBJ_DIR)/$(DICT_NAME).dictionary" "/Volumes/$(DICT_NAME) $(DATE)"
#	@sudo chown -R root:admin "/Volumes/$(DICT_NAME) $(DATE)"
#	@sudo chmod -R ug+rwx "/Volumes/$(DICT_NAME) $(DATE)"
	@cp dmg.VolumeIcon.icns "/Volumes/$(DICT_NAME) $(DATE)/.VolumeIcon.icns"
	@SetFile -a C "/Volumes/$(DICT_NAME) $(DATE)/"
	@cp dmg.background3.png "/Volumes/$(DICT_NAME) $(DATE)/.bg/background.png"
	@osascript -e 'tell application "Finder" to open "$(DICT_NAME) " & do shell script "date +v%Y.%m.%d" & ":"'
	@osascript -e 'tell application "Finder" to set background picture of (icon view options of window 1) to "$(DICT_NAME) " & do shell script "date +v%Y.%m.%d" & ":.bg:background.png"'
	@osascript -e 'tell application "Finder" to set toolbar visible of Finder window 1 to false'
	@osascript -e 'tell application "Finder" to set bounds of Finder window 1 to {100, 100, 660, 480}'
	@osascript -e 'tell application "Finder" to set arrangement of icon view options of Finder window 1 to not arranged'
	@osascript -e 'tell application "Finder" to set current view of Finder window 1 to icon view'
	@osascript -e 'tell application "Finder" to set icon size of icon view options of Finder window 1 to 128'
	@osascript -e 'tell application "Finder" to set position of item 1 of Finder window 1 to {144, 82}'
	@osascript -e 'tell application "Finder" to close Finder window 1'
	@osascript -e 'tell application "Finder" to delay 3'
	@osascript -e 'tell application "Finder" to open "$(DICT_NAME) " & do shell script "date +v%Y.%m.%d" & ":"'
	@osascript -e 'tell application "Finder" to close Finder window 1'
	@osascript -e 'tell application "Finder" to delay 3'
	@echo "- Finishing disk image."
	@hdiutil eject "/Volumes/$(DICT_NAME) $(DATE)"
	@hdiutil convert -ov -format UDBZ DMG.sparseimage -o "$(DICT_NAME) $(DATE).dmg"
	@hdiutil internet-enable "$(DICT_NAME) $(DATE).dmg"
#	@hdiutil unflatten "$(DICT_NAME) $(DATE).dmg" 
#	@/Developer/Tools/Rez /Developer/Headers/FlatCarbon/*.r "SLA.r" -a -o "$(DICT_NAME) $(DATE).dmg" 
#	@hdiutil flatten "$(DICT_NAME) $(DATE).dmg"
	@rm DMG.sparseimage
	@echo "Done."

install:
	@echo "Installing into $(DESTINATION_FOLDER)".
	@mkdir -p $(DESTINATION_FOLDER)
	@ditto --noextattr --norsrc "$(DICT_DEV_KIT_OBJ_DIR)/$(DICT_NAME).dictionary"  "$(DESTINATION_FOLDER)/$(DICT_NAME).dictionary"
	@touch $(DESTINATION_FOLDER)
	@echo "Done."
	@echo "To test the new dictionary, try Dictionary.app."

clean:
	$(RM) -rf $(DICT_DEV_KIT_OBJ_DIR)
	$(RM) -f $(DICT_SRC_PATH)
