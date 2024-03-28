import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'

import {AccountPage} from '#/main/app/account/containers/page'

import {constants as listConst} from '#/main/app/content/list/constants'
import {constants} from '#/main/theme/constants'
import {selectors} from '#/main/theme/account/appearance/store/selectors'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Tool} from '#/main/core/tool'

const AppearanceMain = (props) =>
  <Tool {...props}>
    <AccountPage
      title={trans('appearance', {}, 'tools')}
    >
      <FormData
        name={selectors.FORM_NAME}
        target={['apiv2_theme_preference_update']}
        buttons={true}
        cancel={{
          type: CALLBACK_BUTTON,
          callback: () => props.resetConfig(props.originalData)
        }}
        definition={[
          {
            title: trans('general'),
            primary: true,
            fields: [
              {
                name: 'theme',
                type: 'choice',
                label: trans('theme', {}, 'appearance'),
                required: true,
                displayed: false,
                options: {
                  condensed: true,
                  noEmpty: true,
                  choices: constants.MODES
                }
              }, {
                name: 'themeMode',
                type: 'choice',
                label: trans('theme_mode', {}, 'appearance'),
                required: true,
                options: {
                  condensed: false,
                  noEmpty: true,
                  choices: constants.MODES
                },
                calculated: (data) => !data.themeMode ? constants.MODE_AUTO : data.themeMode,
                onChange: (value) => props.updateConfig('themeMode', value)
              }, {
                name: 'fontSize',
                type: 'choice',
                label: trans('font_size', {}, 'appearance'),
                required: true,
                options: {
                  condensed: false,
                  noEmpty: true,
                  choices: constants.FONT_SIZES
                },
                onChange: (value) => props.updateConfig('fontSize', value)
              }, {
                name: 'fontWeight',
                type: 'choice',
                label: trans('font_weight', {}, 'appearance'),
                required: true,
                options: {
                  condensed: false,
                  //noEmpty: true,
                  choices: constants.FONT_WEIGHTS
                },
                calculated: (data) => parseInt(data.fontWeight),
                onChange: (value) => props.updateConfig('fontWeight', value)
              }, {
                name: 'listMode',
                type: 'choice',
                label: trans('Mode d\'affichage préféré des listes', {}, 'appearance'),
                required: true,
                displayed: false,
                options: {
                  condensed: false,
                  noEmpty: false,
                  choices: listConst.DISPLAY_MODES
                }
              }
            ]
          }
        ]}
      />
    </AccountPage>
  </Tool>

AppearanceMain.propTypes = {
  originalData: T.object.isRequired,
  updateConfig: T.func.isRequired,
  resetConfig: T.func.isRequired
}

export {
  AppearanceMain
}
