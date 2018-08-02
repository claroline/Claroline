import {makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import cloneDeep from 'lodash/cloneDeep'
import find from 'lodash/find'
import findIndex from 'lodash/findIndex'
import {moveItemInArray} from '#/plugin/blog/resources/blog/toolbar/utils'
import {
  BLOG_OPTIONS_WIDGET_VISIBILITY, 
  BLOG_OPTIONS_WIDGET_UP, 
  BLOG_OPTIONS_WIDGET_DOWN
} from '#/plugin/blog/resources/blog/editor/store/actions'

const reducer = {
  options: makeFormReducer('blog.data.options', {}, {
    pendingChanges: makeReducer({}, {
      [BLOG_OPTIONS_WIDGET_VISIBILITY]: () => true,
      [BLOG_OPTIONS_WIDGET_UP]: () => true,
      [BLOG_OPTIONS_WIDGET_DOWN]: () => true
    }),
    data: makeReducer({}, {
      [BLOG_OPTIONS_WIDGET_VISIBILITY]: (state, action) => {
        const data = cloneDeep(state)
        const widget = find(data.widgetOrder, ['id', action.id])
        if(widget){
          widget.visibility = !widget.visibility
        }else{
          data.widgetOrder.push({'nameTemplate': action.name, 'visibility': true, 'id': action.id})
        }
        return data
      },
      [BLOG_OPTIONS_WIDGET_UP]: (state, action) => {
        const data = cloneDeep(state)
        const index = findIndex(data.widgetOrder, ['id', action.id])
        if(index != -1){
          moveItemInArray(data.widgetOrder, index, index - 1)
        }else{
          data.widgetOrder.push({'nameTemplate': action.name, 'visibility': true, 'id': action.id})
        }
        return data
      },
      [BLOG_OPTIONS_WIDGET_DOWN]: (state, action) => {
        const data = cloneDeep(state)
        const index = findIndex(data.widgetOrder, ['id', action.id])
        if(index != -1){
          moveItemInArray(data.widgetOrder, index, index + 1)
        }else{
          data.widgetOrder.push({'nameTemplate': action.name, 'visibility': true, 'id': action.id})
        }
        return data
      }
    })
  })
}

export {
  reducer
}