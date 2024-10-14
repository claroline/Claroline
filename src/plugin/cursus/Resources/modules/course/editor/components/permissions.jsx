import React from 'react'
import {useDispatch} from 'react-redux'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'
import {selectors} from '#/plugin/cursus/course/store'
import {actions as formActions} from '#/main/app/content/form/store'

const CourseEditorPermissions = () => {
  const dispatch = useDispatch()
  const update = (name, prop, value) => {
    dispatch(formActions.updateProp(name, prop, value))
  }

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
              name: 'restrictions._restrictUsers',
              type: 'boolean',
              label: trans('restrict_users_count'),
              calculated: (course) => !!get(course, 'restrictions.users') || get(course, 'restrictions._restrictUsers'),
              onChange: (value) => {
                if (!value) {
                  update(selectors.FORM_NAME, 'restrictions.users', null)
                }
              },
              linked: [
                {
                  name: 'restrictions.users',
                  type: 'number',
                  label: trans('users_count'),
                  required: true,
                  displayed: (course) => get(course, 'restrictions.users') || get(course, 'restrictions._restrictUsers'),
                  options: {
                    min: 0
                  }
                }
              ]
            }, {
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
          title: trans('organizations'),
          hideTitle: true,
          primary: true,
          fields: [
            {
              name: 'organizations',
              label: trans('organizations'),
              type: 'organizations'
            }
          ]
        }
      ]}
    />
  )
}

export {
  CourseEditorPermissions
}
