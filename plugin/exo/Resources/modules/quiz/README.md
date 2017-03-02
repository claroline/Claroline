Initialization
--------------

The whole quiz data is processed to make it suitable for edition:

1. Get raw server data (currently embedded in an HTML attribute)
2. Normalize it (flatten structure to ease updates)
3. Decorate it:
   - Add sections to hold editor state
   - Add convenience properties where selectors wouldn't be practical
   - Add UI flags and defaults for missing values
4. Initialize the store with the resulting data structure

Rendering
---------

1. Connect the root editor component to the store
2. Get data from the store, possibly shaping/aggregating it with selectors
3. Pass data down the component tree as properties along with update callbacks

Updating
--------

On update callbacks, produce a new state through specific reducers:

- Creation: produce a new sub-state, merge it with current one and validate the result
- Update: sanitize incoming data, merge it with current state and validate the result
- Deletion: remove portions of the state and validate if necessary
