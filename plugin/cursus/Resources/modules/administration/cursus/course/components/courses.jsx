import React from 'react'

import {ListData} from '#/main/app/content/list/containers/data'

import {CourseList} from '#/plugin/cursus/administration/cursus/course/components/course-list'

const Courses = () =>
  <ListData
    name="courses.list"
    fetch={{
      url: ['apiv2_cursus_course_list'],
      autoload: true
    }}
    primaryAction={CourseList.open}
    delete={{
      url: ['apiv2_cursus_course_delete_bulk']
    }}
    definition={CourseList.definition}
  />

export {
  Courses
}
