#!/bin/bash 

set -o pipefail

workflow="$1"
shift;
logfile="$1"
shift;
title="$1"
shift;


gnudate() {
    if hash gdate 2>/dev/null; then
        gdate "$@"
    else
        date "$@"
    fi
}

echo "$(gnudate -Ins) [PASSTHRU] $workflow $title: $@"
start_time="$(gnudate -u +%s.%N)"

function timer()
{
    exit_code="$?"
    end_time="$(gnudate -u +%s.%N)"
    echo "$(gnudate -Ins) [PASSTHRU] $workflow $title: ELAPSE $(awk -v a="$end_time" -v b="$start_time" 'BEGIN { printf "%s", a-b }' </dev/null)s"
    echo "$(gnudate -Ins) [PASSTHRU] $workflow $title: EXITCODE $exit_code"
    exit "$exit_code"
}

trap timer EXIT

$@ 2>&1 | tee -a "$logfile"
