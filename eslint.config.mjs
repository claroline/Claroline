import {defineConfig, globalIgnores} from 'eslint/config'
import react from 'eslint-plugin-react'
import stylistic from '@stylistic/eslint-plugin'
import globals from 'globals'
import path from 'node:path'
import {fileURLToPath} from 'node:url'
import js from '@eslint/js'
import {FlatCompat} from '@eslint/eslintrc'

const __filename = fileURLToPath(import.meta.url)
const __dirname = path.dirname(__filename)

const compat = new FlatCompat({
    baseDirectory: __dirname,
    recommendedConfig: js.configs.recommended,
    allConfig: js.configs.all
})

export default defineConfig([
    globalIgnores(['**/modules/plugin.js']),

    js.configs.recommended,
    stylistic.configs['disable-legacy'],

    {
        extends: compat.extends(
          'plugin:react/recommended'
        ),

        plugins: {
            react,
            '@stylistic': stylistic
        },

        languageOptions: {
            globals: {
                ...globals.browser
            },

            ecmaVersion: 'latest',
            sourceType: 'module',

            parserOptions: {
                ecmaFeatures: {
                    jsx: true,
                    spread: true
                }
            }
        },

        settings: {
            react: {
                version: '18.2'
            }
        },

        rules: {
            '@stylistic/indent': ['error', 2, {SwitchCase: 1}],
            '@stylistic/linebreak-style': ['error', 'unix'],
            '@stylistic/quotes': ['error', 'single'],
            '@stylistic/semi': ['error', 'never'],
            '@stylistic/comma-dangle': ['error', 'never'],
            '@stylistic/space-before-function-paren': ['error', {
                anonymous: 'always',
                named: 'never'
            }],
            '@stylistic/eol-last': ['error', 'always'],
            'react/prop-types': ['error', {
                skipUndeclared: true
            }],
            'react/display-name': 'off'
        }
    }
])