import {FORM_SUBMIT_SUCCESS, makeFormReducer} from "#/main/app/content/form/store";
import {makeListReducer}                      from "#/main/app/content/list/store";
import {combineReducers, makeReducer}         from "#/main/app/store/reducer";
import {selectors}                            from "#/main/core/administration/connection-messages/store/selectors";
import {makeInstanceAction}                   from "#/main/app/store/actions";
import {TOOL_LOAD}                            from "#/main/core/tool/store";

const reducer = combineReducers({

  messages: combineReducers({
    list: makeListReducer(selectors.STORE_NAME+'.messages.list', {}, {
      invalidated: makeReducer(false, {
        [FORM_SUBMIT_SUCCESS+'/'+selectors.STORE_NAME+'.messages.current']: () => true,
        [makeInstanceAction(TOOL_LOAD, 'connection_messages')]: () => true
      })
    }),
    current: makeFormReducer(selectors.STORE_NAME+'.messages.current')
  })
})

export { reducer }
