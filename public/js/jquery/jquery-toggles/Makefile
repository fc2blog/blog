.DEFAULT: clean build
.PHONY: clean distclean

build: \
	setup \
	toggles.min.js \

clean:
	rm -f toggles.min.js

lib:
	mkdir lib

distclean: clean
	rm -f lib/compiler.jar lib/jquery-1.8-extern.js

toggles.min.js: toggles.js

setup: \
	lib \
	lib/jquery-1.8-extern.js \
	lib/compiler.jar \

lib/jquery-1.8-extern.js:
	wget -O $@ http://closure-compiler.googlecode.com/svn/trunk/contrib/externs/jquery-1.8.js

lib/compiler.jar:
	wget -O- http://dl.google.com/closure-compiler/compiler-latest.tar.gz | tar -xz -C lib compiler.jar

toggles.min.js:
	java -jar lib/compiler.jar --output_wrapper='(function($$){%output%})(jQuery);' --compilation_level ADVANCED_OPTIMIZATIONS --externs lib/jquery-1.8-extern.js < $< > $@
