import {createSelector} from 'reselect'
import get from 'lodash/get'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

const STORE_NAME = 'icap_lesson'
const LIST_NAME = STORE_NAME + '.chapters'

const CHAPTER_EDIT_FORM_NAME = STORE_NAME + '.chapter_form'

const store = (state) => state[STORE_NAME]

const lesson = resourceSelectors.resource

const placeholders = createSelector(
  [store],
  (store) => store.placeholders
)

const chapters = createSelector(
  [store],
  (store) => store.chapters
)

const tree = createSelector(
  [chapters],
  (chapters) => {
    function buildTree(source, currentChildren, currentSlug) {
      const elements = source.filter(elem => elem.parentSlug === currentSlug)
      elements.forEach(elem => {
        // build a tree node for this element
        const treeNode = {
          id: elem.id,
          level: elem.level,
          slug: elem.slug,
          title: elem.title,
          children: []
        }
        // add it to the parent children array
        currentChildren.push(treeNode)
        // continue recursively, it will stop when no element with the specified parentUUID can be found
        buildTree(source, treeNode.children, elem.slug)
      })
    }

    const result = []
    buildTree(chapters, result, null)

    return result[0]
  }
)

const root = createSelector(
  [chapters],
  (chapters) => chapters.find(chapter => null === chapter.parentSlug)
)

const pages = createSelector(
  [chapters],
  (chapters) => chapters.filter(chapter => null !== chapter.parentSlug)
)

const currentPageSlug = createSelector(
  [store],
  (store) => store.currentPage
)

const currentPageIndex = createSelector(
  [currentPageSlug, pages],
  (currentPageSlug, pages) => {
    if (!currentPageSlug) {
      return 0
    }

    return pages.findIndex(page => page.slug === currentPageSlug)
  }
)

const currentPage = createSelector(
  [currentPageIndex, pages],
  (currentPageIndex, pages = []) => pages[currentPageIndex]
)

const showOverview = createSelector(
  [lesson],
  (lesson) => get(lesson, 'display.showOverview', false)
)

const showMeta = createSelector(
  [lesson],
  (lesson) => get(lesson, 'display.showMeta', false)
)

const showNavigation = createSelector(
  [lesson],
  (lesson) => get(lesson, 'display.navigation', false)
)

const numbering = createSelector(
  [lesson],
  (lesson) => get(lesson, 'display.numbering', null)
)

const nextPage = createSelector(
  [currentPage, pages],
  (current, pages) => {
    if (current.nextSlug) {
      return pages.find(page => page.slug === current.nextSlug)
    }

    return null
  }
)

const previousPage = createSelector(
  [currentPage, pages],
  (current, pages) => {
    if (current.previousSlug) {
      return pages.find(page => page.slug === current.previousSlug)
    }

    return null
  }
)

export const selectors = {
  STORE_NAME,
  LIST_NAME,
  CHAPTER_EDIT_FORM_NAME,

  lesson,
  placeholders,
  root,
  tree,
  currentPage,
  currentPageSlug,
  currentPageIndex,
  pages,
  showOverview,
  showMeta,
  showNavigation,
  numbering,
  nextPage,
  previousPage
}
