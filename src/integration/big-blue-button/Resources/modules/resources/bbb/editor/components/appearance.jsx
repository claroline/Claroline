import React, {useCallback} from 'react'
import {useDispatch} from 'react-redux'

import {actions as formActions} from '#/main/app/content/form'
import {trans} from '#/main/app/intl'
import {ResourceEditorAppearance, selectors as editorSelectors} from '#/main/core/resource/editor'

import {constants} from '#/integration/big-blue-button/resources/bbb/constants'

const BBBEditorAppearance = () => {
  const dispatch = useDispatch()
  const updateProp = useCallback((prop, value) => {
    dispatch(formActions.updateProp(editorSelectors.STORE_NAME, 'resource.'+prop, value))
  }, [editorSelectors.STORE_NAME])

  return (
    <ResourceEditorAppearance
      definition={[
        {
          name: 'meeting',
          title: trans('display_parameters'),
          primary: true,
          hideTitle: true,
          fields: [
            {
              name: 'resource.newTab',
              type: 'boolean',
              label: trans('open_meeting_in_new_tab', {}, 'bbb'),
              linked: [
                {
                  name: 'resource.ratioList',
                  type: 'choice',
                  label: trans('display_ratio_list'),
                  options: {
                    multiple: false,
                    condensed: false,
                    choices: constants.DISPLAY_RATIO_LIST
                  },
                  displayed: (bbb) => !bbb.resource.newTab,
                  onChange: (ratio) => updateProp('ratio', parseFloat(ratio))
                }, {
                  name: 'resource.ratio',
                  type: 'number',
                  label: trans('display_ratio'),
                  options: {
                    min: 0,
                    unit: '%'
                  },
                  displayed: (bbb) => !bbb.resource.newTab,
                  onChange: () => updateProp('ratioList', null)
                }
              ]
            }
          ]
        }
      ]}
    />
  )
}

export {
  BBBEditorAppearance
}
