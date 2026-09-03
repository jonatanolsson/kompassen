#!/bin/bash

# Flux Pro Installer
# Usage: curl -s https://raw.githubusercontent.com/novislab/flux-pro/main/install.sh | bash

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}"
echo "  _____ _    _   ___  __  ____  ____   ___  "
echo " |  ___| |  | | | \ \/ / |  _ \|  _ \ / _ \ "
echo " | |_  | |  | | | |\  /  | |_) | |_) | | | |"
echo " |  _| | |__| |_| |/  \  |  __/|  _ <| |_| |"
echo " |_|   |_____\___//_/\_\ |_|   |_| \_\\\\___/ "
echo -e "${NC}"
echo -e "${GREEN}Flux Pro Installer v2.11.1${NC}"
echo ""

# Check if we're in a Laravel project
if [ ! -f "composer.json" ]; then
    echo -e "${RED}Error: composer.json not found. Please run this from your Laravel project root.${NC}"
    exit 1
fi

if [ ! -f "artisan" ]; then
    echo -e "${RED}Error: This doesn't appear to be a Laravel project (artisan not found).${NC}"
    exit 1
fi

#echo -e "${YELLOW}Step 1/4: Adding Flux Pro repository to composer.json...${NC}"

# Check if repository already exists
#
#if grep -q "novislab/flux-pro" composer.json; then
#    echo -e "${GREEN}Repository already configured in composer.json${NC}"
#else
#    # Add repository to composer.json using php
#    php -r "
#        \$json = json_decode(file_get_contents('composer.json'), true);
#        if (!isset(\$json['repositories'])) {
#            \$json['repositories'] = [];
#        }#
#
#        // Check if repo already exists
#        \$exists = false;
#        foreach (\$json['repositories'] as \$repo) {
#            if (isset(\$repo['url']) && strpos(\$repo['url'], 'novislab/flux-pro') !== false) {
#                \$exists = true;
#                break;
#            }
#        }#
#
#        if (!\$exists) {
#            \$json['repositories'][] = [
#                'type' => 'vcs',
#                'url' => 'https://github.com/novislab/flux-pro'
#            ];
#        }##
#
 #       file_put_contents('composer.json', json_encode(\$json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
  #  "
 #   echo -e "${GREEN}Repository added to composer.json${NC}"
#sfi

echo -e "${YELLOW}Step 2/4: Installing Flux Pro via Composer...${NC}"
composer require livewire/flux-pro:dev-main --no-interaction

echo -e "${YELLOW}Step 3/4: Configuring Tailwind CSS...${NC}"

# Find app.css file
CSS_FILE=""
if [ -f "resources/css/app.css" ]; then
    CSS_FILE="resources/css/app.css"
elif [ -f "resources/sass/app.scss" ]; then
    CSS_FILE="resources/sass/app.scss"
elif [ -f "resources/css/main.css" ]; then
    CSS_FILE="resources/css/main.css"
fi

if [ -n "$CSS_FILE" ]; then
    # Check if Flux CSS is already imported
    if grep -q "livewire/flux/dist/flux.css" "$CSS_FILE"; then
        echo -e "${GREEN}Flux CSS already configured in $CSS_FILE${NC}"
    else
        # Check if file has tailwindcss import
        if grep -q "@import \"tailwindcss\"" "$CSS_FILE" || grep -q "@import 'tailwindcss'" "$CSS_FILE"; then
            # Add after tailwindcss import
            sed -i '/@import.*tailwindcss/a @import "../../vendor/livewire/flux/dist/flux.css";' "$CSS_FILE"
            echo -e "${GREEN}Added Flux CSS import to $CSS_FILE${NC}"
        else
            # Prepend to file
            echo -e '@import "../../vendor/livewire/flux/dist/flux.css";\n' | cat - "$CSS_FILE" > temp && mv temp "$CSS_FILE"
            echo -e "${GREEN}Added Flux CSS import to $CSS_FILE${NC}"
        fi
    fi

    # Add base Flux source directive if not present
    if ! grep -q "livewire/flux/stubs" "$CSS_FILE"; then
        echo '' >> "$CSS_FILE"
        echo "@source '../../vendor/livewire/flux/stubs/**/*.blade.php';" >> "$CSS_FILE"
        echo -e "${GREEN}Added Flux source directive to $CSS_FILE${NC}"
    fi

    # Add Flux Pro source directive if not present
    if ! grep -q "flux-pro/stubs" "$CSS_FILE"; then
        echo "@source '../../vendor/livewire/flux-pro/stubs/**/*.blade.php';" >> "$CSS_FILE"
        echo -e "${GREEN}Added Flux Pro source directive to $CSS_FILE${NC}"
    fi
else
    echo -e "${YELLOW}Could not find CSS file. Please manually add to your CSS:${NC}"
    echo -e "${BLUE}@import \"../../vendor/livewire/flux/dist/flux.css\";${NC}"
    echo -e "${BLUE}@source '../../vendor/livewire/flux/stubs/**/*.blade.php';${NC}"
    echo -e "${BLUE}@source '../../vendor/livewire/flux-pro/stubs/**/*.blade.php';${NC}"
fi

echo -e "${YELLOW}Step 4/4: Publishing assets (optional)...${NC}"
echo -e "${BLUE}Skipping auto-publish. Run manually if needed:${NC}"
echo -e "${BLUE}php artisan vendor:publish --tag=flux-pro-views${NC}"

echo ""
echo -e "${GREEN}=============================================${NC}"
echo -e "${GREEN}  Flux Pro v2.11.1 installed successfully!  ${NC}"
echo -e "${GREEN}=============================================${NC}"
echo ""
echo -e "You can now use Flux Pro components in your Blade templates:"
echo ""
echo -e "${BLUE}<flux:editor wire:model=\"content\" />${NC}"
echo -e "${BLUE}<flux:date-picker wire:model=\"date\" />${NC}"
echo -e "${BLUE}<flux:kanban>...</flux:kanban>${NC}"
echo ""
echo -e "Documentation: ${BLUE}https://github.com/novislab/flux-pro${NC}"
echo ""
