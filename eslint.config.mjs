import { defineConfig, globalIgnores } from "eslint/config";
import react from "eslint-plugin-react";
import globals from "globals";
import path from "node:path";
import { fileURLToPath } from "node:url";
import js from "@eslint/js";
import { FlatCompat } from "@eslint/eslintrc";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const compat = new FlatCompat({
    baseDirectory: __dirname,
    recommendedConfig: js.configs.recommended,
    allConfig: js.configs.all
});

export default defineConfig([globalIgnores(["**/modules/plugin.js"]), {
    extends: compat.extends("eslint:recommended", "plugin:react/recommended"),

    plugins: {
        react,
    },

    languageOptions: {
        globals: {
            ...globals.browser,
        },

        ecmaVersion: "latest",
        sourceType: "module",

        parserOptions: {
            ecmaFeatures: {
                jsx: true,
                spread: true,
            },
        },
    },

    settings: {
        react: {
            version: "15.6",
        },
    },

    rules: {
        indent: ["error", 2, {
            SwitchCase: 1,
        }],

        "linebreak-style": ["error", "unix"],
        quotes: ["error", "single"],
        semi: ["error", "never"],
        "comma-dangle": ["error", "never"],

        "space-before-function-paren": ["error", {
            anonymous: "always",
            named: "never",
        }],

        "react/prop-types": ["error", {
            skipUndeclared: true,
        }],

        "react/display-name": "off",
    },
}]);