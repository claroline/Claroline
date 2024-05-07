import React from 'react'
import {useSelector} from 'react-redux'

import {ResourceEditor} from '#/main/core/resource'
import {selectors} from '#/plugin/forum/resources/forum/store'
import {ForumEditorAppearance} from '#/plugin/forum/resources/forum/editor/components/appearance'
import {trans} from '#/main/app/intl'
import {ForumEditorModeration} from '#/plugin/forum/resources/forum/editor/components/moderation'

const ForumEditor = () => {
  const forum = useSelector(selectors.forum)

  return (
    <ResourceEditor
      additionalData={() => ({
        resource: forum
      })}
      appearancePage={ForumEditorAppearance}
      pages={[
        {
          name: 'moderation',
          title: trans('moderation', {}, 'forum'),
          component: ForumEditorModeration
        }
      ]}
    />
  )
}

export {
  ForumEditor
}
