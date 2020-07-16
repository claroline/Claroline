import {trans} from '#/main/app/intl/translation'

import {CourseDisplay} from '#/plugin/cursus/data/types/course/components/display'
import {CourseInput} from '#/plugin/cursus/data/types/course/components/input'

const dataType = {
  name: 'course',
  meta: {
    creatable: false,
    icon: 'fa fa-fw fa fa-graduation-cap',
    label: trans('course', {}, 'data'),
    description: trans('course_desc', {}, 'data')
  },
  components: {
    details: CourseDisplay,
    input: CourseInput
  }
}

export {
  dataType
}
