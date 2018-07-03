import cloneDeep from 'lodash/cloneDeep'
import {trans} from '#/main/core/translation'

/**
 * Transforme l'arbre du cours provenant de l'API pour que le composant Summary puisse l'utiliser
 *
 * @param tree
 */
export const normalizeTree = (tree, lessonId, deleteFunction, canEdit) => {

  const copy = cloneDeep(tree)

  let elems = normalizeTreeNode(copy.children, lessonId, deleteFunction, canEdit)
  elems.push({
    label: trans('chapter_creation', {}, 'icap_lesson'),
    target: '/new',
    icon: 'fa fa-fw fa-plus',
    type: 'link'
  })

  return {
    id: tree.id,
    slug: tree.slug,
    children: elems
  }
}

const normalizeTreeNode = (node, lessonId, deleteFunction, canEdit) => {

  return node.map((elem) => {



    const element = {
      type: 'link',
      target: `/${elem['slug']}`,
      label: elem['title'],
      additional: [
        {
          type: 'link',
          target: `/${elem['slug']}/edit`,
          label: trans('edit_chapter_button', {}, 'icap_lesson'),
          icon: 'fa fa-pencil',
          displayed: canEdit
        },
        {
          type: 'callback',
          icon: 'fa fa-trash',
          label: trans('delete'),
          callback: () => deleteFunction(lessonId, elem['slug'], elem['title']),
          displayed: canEdit
        }
      ]
    }

    if (elem.children.length > 0) {
      element.children = normalizeTreeNode(elem.children, lessonId, deleteFunction)
    }

    return element
  })
}

export const buildParentChapterChoices = (tree, chapter) => {
  let chapterSlug = chapter ? chapter.slug : null

  let flattenedChapters = {}
  flattenedChapters[tree['slug']] = trans('Root', {}, 'icap_lesson')

  if (tree['children'] && Array.isArray(tree['children']) && tree['children'].length > 0) {

    flattenedChapters = Object.assign(flattenedChapters, buildFlattenedChapterChoices(tree['children'], chapterSlug))
  }

  return flattenedChapters
}

const buildFlattenedChapterChoices = (items, chapterSlug) => {
  let flattenedChapters = {}
  items.forEach(item => {

    if (item['slug'] !== chapterSlug) {
      flattenedChapters[item['slug']] = item['title']
      if (item['children'] && Array.isArray(item['children']) && item['children'].length > 0) {
        flattenedChapters = Object.assign(flattenedChapters, buildFlattenedChapterChoices(item['children']))
      }
    }
  })

  return flattenedChapters
}