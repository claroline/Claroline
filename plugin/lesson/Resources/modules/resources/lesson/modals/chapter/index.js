import {registry} from '#/main/app/modals/registry'

import {ChapterDeleteModal} from '#/plugin/lesson/resources/lesson/modals/chapter/components/delete'

const MODAL_LESSON_CHAPTER_DELETE = 'MODAL_LESSON_CHAPTER_DELETE'

registry.add(MODAL_LESSON_CHAPTER_DELETE, ChapterDeleteModal)

export {
  MODAL_LESSON_CHAPTER_DELETE
}