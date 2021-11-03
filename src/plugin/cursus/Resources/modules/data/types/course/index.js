import {trans} from '#/main/app/intl/translation'

import {CourseDisplay} from '#/plugin/cursus/data/types/course/components/display'
import {CourseInput} from '#/plugin/cursus/data/types/course/components/input'
import {CourseFilter} from '#/plugin/cursus/data/types/course/components/filter'

const dataType = {
  name: 'course',
  meta: {
    creatable: false,
    icon: 'fa fa-fw fa fa-graduation-cap',
    label: trans('course', {}, 'data'),
    description: trans('course_desc', {}, 'data')
  },
  render: (raw) => raw ? raw.name : null,
  components: {
    details: CourseDisplay,
    input: CourseInput,
    search: CourseFilter
  }
}

export {
  dataType
}
