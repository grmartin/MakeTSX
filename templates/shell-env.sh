#!/bin/bash

# Detect a RoyalTS(X) session and load standard login shell scripts first.
if [ ! -z ${ROYAL_FILE_PATH+x} ] 
then 
	echo "RoyalTS session found, importing standard environment info." 
	# Read in default profile scripts. Tilde expansion cant be trusted here yet.
	test -r "/etc/bashrc" -a -f "/etc/bashrc" && . /etc/bashrc 2> /dev/null
	test -r "/etc/profile" -a -f "/etc/profile" && . /etc/profile 2> /dev/null
	test -r "$HOME/.bashrc" -a -f "$HOME/.bashrc" && . "$HOME/.bashrc" 2> /dev/null
	test -r "$HOME/.bash_profile" -a -f "$HOME/.bash_profile" && . "$HOME/.bash_profile" 2> /dev/null
	
	# invoke macOS path building app.
	if [ -x /usr/libexec/path_helper ]; then
    	eval `/usr/libexec/path_helper -s`
	fi
fi

if [ -z ${TMPL($envVarPrefix)_ROOT_ENV_SOURCE+x} ]
then
	# Get script location, resolve if a link.
	export TMPL($envVarPrefix)_ROOT_ENV_SOURCE="${BASH_SOURCE[0]}"
	while [ -h "$TMPL($envVarPrefix)_ROOT_ENV_SOURCE" ]; do
	  export TMPL($envVarPrefix)_ROOT_DIR="$( cd -P "$( dirname "$TMPL($envVarPrefix)_ROOT_ENV_SOURCE" )" && pwd )"
	  export TMPL($envVarPrefix)_ROOT_ENV_SOURCE="$(readlink "$TMPL($envVarPrefix)_ROOT_ENV_SOURCE")"
	  [[ ${TMPL($envVarPrefix)_ROOT_ENV_SOURCE} != /* ]] && TMPL($envVarPrefix)_ROOT_ENV_SOURCE="$TMPL($envVarPrefix)_ROOT_DIR/$TMPL($envVarPrefix)_ROOT_ENV_SOURCE"
	done
	export TMPL($envVarPrefix)_ROOT_DIR="$( cd -P "$( dirname "$TMPL($envVarPrefix)_ROOT_ENV_SOURCE" )" && pwd )"
fi

export TMPL($envVarPrefix)_HOME=$HOME
export TMPL($envVarPrefix)_BASE_PATH=$PATH
export TMPL($envVarPrefix)_TOOL_PATH=$TMPL($envVarPrefix)_ROOT_DIR

function __flat_date {
	local returnVal=$(date +%Y%m%d%H%M);
	echo "$returnVal"
}

function edit_env {
	bbedit $TMPL($envVarPrefix)_ROOT_DIR/$TMPL($envVarPrefix)_ROOT_ENV_SOURCE $@
}

function refresh_env {
	. $TMPL($envVarPrefix)_ROOT_DIR/$TMPL($envVarPrefix)_ROOT_ENV_SOURCE $@
}
