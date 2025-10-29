import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'
import {LinkedOrganizations} from '#/main/community/components/linked-organizations'

import {selectors} from '#/plugin/cursus/course/editor/store'

const CourseEditorPermissions = () => {
  const course = useSelector(selectors.course)

  return (
    <EditorPage
      title={trans('permissions')}
      help={trans('course_permissions_help', {}, 'cursus')}
      managerOnly={true}
      definition={[
        {
          name: 'public',
          title: trans('public_course', {}, 'cursus'),
          primary: true,
          fields: [
            {
              name: 'meta.public',
              type: 'boolean',
              label: trans('make_course_public', {}, 'cursus'),
              help: [
                trans('make_course_public_help', {}, 'cursus')
              ]
            }
          ]
        }, {
          name: 'organizations',
          title: trans('organizations', {}, 'community'),
          description: trans('course_organizations_desc', {}, 'cursus'),
          primary: true,
          render: () => (
            <LinkedOrganizations
              autoload={!!course && !!course.id}
              name={`${selectors.STORE_NAME}.organizations`}
              description={trans('course_organizations_desc', {}, 'cursus')}
              url={['apiv2_cursus_course_list_organizations', {id: course.id}]}
              addUrl={['apiv2_cursus_course_add_organizations', {id: course.id}]}
              removeUrl={['apiv2_cursus_course_remove_organizations', {id: course.id}]}
            />
          )
        }
      ]}
    />
  )
}

export {
  CourseEditorPermissions
}
