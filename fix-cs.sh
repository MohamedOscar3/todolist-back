#!/bin/bash

# Script to run PHP-CS-Fixer with common options

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Default values
DRY_RUN=false
DIFF=false
VERBOSE=false
SPECIFIC_PATH=""

# Help function
show_help() {
    echo "PHP-CS-Fixer wrapper script"
    echo ""
    echo "Usage: ./fix-cs.sh [options] [path]"
    echo ""
    echo "Options:"
    echo "  -h, --help     Show this help message"
    echo "  -d, --dry-run  Run in dry-run mode (don't modify files)"
    echo "  -f, --diff     Show diff of changes"
    echo "  -v, --verbose  Show verbose output"
    echo ""
    echo "If path is specified, only that path will be checked/fixed"
    echo "Otherwise, all paths defined in .php-cs-fixer.php will be processed"
}

# Parse arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        -h|--help)
            show_help
            exit 0
            ;;
        -d|--dry-run)
            DRY_RUN=true
            shift
            ;;
        -f|--diff)
            DIFF=true
            shift
            ;;
        -v|--verbose)
            VERBOSE=true
            shift
            ;;
        *)
            SPECIFIC_PATH="$1"
            shift
            ;;
    esac
done

# Build command
CMD="./vendor/bin/php-cs-fixer fix"

if [ "$DRY_RUN" = true ]; then
    CMD="$CMD --dry-run"
fi

if [ "$DIFF" = true ]; then
    CMD="$CMD --diff"
fi

if [ "$VERBOSE" = true ]; then
    CMD="$CMD -v"
fi

if [ ! -z "$SPECIFIC_PATH" ]; then
    CMD="$CMD $SPECIFIC_PATH"
fi

# Run the command
echo -e "${YELLOW}Running:${NC} $CMD"
eval $CMD

# Check exit status
STATUS=$?
if [ $STATUS -eq 0 ]; then
    echo -e "${GREEN}PHP-CS-Fixer completed successfully!${NC}"
else
    echo -e "${RED}PHP-CS-Fixer encountered issues. Exit code: $STATUS${NC}"
fi

exit $STATUS
