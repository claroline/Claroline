import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {EditorPage} from '#/main/app/editor'
import {selectors as editorSelectors} from '#/main/core/tool/editor'

import {SkillsFrameworkCreation} from '#/main/evaluation/skills-framework/components/creation'
import {selectors} from '#/main/evaluation/tools/evaluation/editor/skill/store'

const EditorSkillList = () => {
  const editorPath = useSelector(editorSelectors.path)

  return (
    <EditorPage
      title={trans('skills_frameworks', {}, 'evaluation')}
      help={trans('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.')}
    >
      <ListData
        name={selectors.LIST_NAME}
        fetch={{
          url: ['apiv2_skills_framework_list'],
          autoload: true
        }}
        delete={{
          url: ['apiv2_skills_framework_delete'],
          disabled: (rows) => -1 === rows.findIndex(row => hasPermission('administrate', row))
        }}
        primaryAction={(row) => ({
          type: LINK_BUTTON,
          target: editorPath+'/skills/'+row.id
        })}
        definition={[
          {
            name: 'name',
            type: 'string',
            label: trans('name'),
            displayed: true,
            primary: true
          }, {
            name: 'description',
            type: 'string',
            label: trans('description'),
            displayed: true,
            sortable: false,
            options: {long: true}
          }
        ]}
      >
        <p className="text-center my-5">
          <span className="h1 fa fa-bullseye mb-3 text-body-tertiary" />
          <b className="h5 d-block">Votre espace d&apos;activités n&apos;est lié à aucun référentiel de compétences.</b>
          <span className="text-body-secondary">Ajoutez un référentiel pour pouvoir associer vos Séquences à des compétences et vos activités à des Capacités.</span>
        </p>

        <SkillsFrameworkCreation />
      </ListData>
    </EditorPage>
  )
}

export {
  EditorSkillList
}
