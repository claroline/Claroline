import React from 'react'

import {TreeData} from '#/main/app/content/tree/containers/data'

import {trans} from '#/main/core/translation'

import {CursusList} from '#/plugin/cursus/administration/cursus/cursus/components/cursus-list'

const Cursus = () =>
  <TreeData
    name="cursus.list"
    primaryAction={CursusList.open}
    fetch={{
      url: ['apiv2_cursus_list'],
      autoload: true
    }}
    delete={{
      url: ['apiv2_cursus_delete_bulk']
    }}
    definition={CursusList.definition}
    actions={(rows) => [
      {
        type: 'link',
        icon: 'fa fa-fw fa-plus',
        label: trans('create_cursus_child', {}, 'cursus'),
        scope: ['object'],
        target: 'cursus/form/parent/' + rows[0].id
      }
    //   {
    //     type: 'callback',
    //     icon: 'fa fa-fw fa-tasks',
    //     label: trans('add_course_to_cursus', {}, 'cursus'),
    //     scope: ['object'],
    //     callback: () => console.log(rows[0])
    //   }
    ]}
    card={(row) => ({
      icon: 'fa fa-database',
      title: row.title,
      subtitle: row.code,
      flags: [
        row.meta.course && ['fa fa-tasks', trans('course', {}, 'cursus')]
      ].filter(flag => !!flag)
    })}
  />

export {
  Cursus
}
