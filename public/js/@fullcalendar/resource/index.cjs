'use strict';

Object.defineProperty(exports, '__esModule', { value: true });

var index_cjs = require('@fullcalendar/core/index.cjs');
var premiumCommonPlugin = require('@fullcalendar/premium-common/index.cjs');
var internal_cjs = require('@fullcalendar/core/internal.cjs');
var internalCommon = require('./internal-common.cjs');
require('@fullcalendar/core/preact.cjs');

function _interopDefaultLegacy (e) { return e && typeof e === 'object' && 'default' in e ? e : { 'default': e }; }

var premiumCommonPlugin__default = /*#__PURE__*/_interopDefaultLegacy(premiumCommonPlugin);

function massageEventDragMutation(eventMutation, hit0, hit1) {
    let resource0 = hit0.dateSpan.resourceId;
    let resource1 = hit1.dateSpan.resourceId;
    if (resource0 && resource1 &&
        resource0 !== resource1) {
        eventMutation.resourceMutation = {
            matchResourceId: resource0,
            setResourceId: resource1,
        };
    }
}
/*
TODO: all this would be much easier if we were using a hash!
*/
function applyEventDefMutation(eventDef, mutation, context) {
    let resourceMutation = mutation.resourceMutation;
    if (resourceMutation && computeResourceEditable(eventDef, context)) {
        let index = eventDef.resourceIds.indexOf(resourceMutation.matchResourceId);
        if (index !== -1) {
            let resourceIds = eventDef.resourceIds.slice(); // copy
            resourceIds.splice(index, 1); // remove
            if (resourceIds.indexOf(resourceMutation.setResourceId) === -1) { // not already in there
                resourceIds.push(resourceMutation.setResourceId); // add
            }
            eventDef.resourceIds = resourceIds;
        }
    }
}
/*
HACK
TODO: use EventUi system instead of this
*/
function computeResourceEditable(eventDef, context) {
    let { resourceEditable } = eventDef;
    if (resourceEditable == null) {
        let source = eventDef.sourceId && context.getCurrentData().eventSources[eventDef.sourceId];
        if (source) {
            resourceEditable = source.extendedProps.resourceEditable; // used the Source::extendedProps hack
        }
        if (resourceEditable == null) {
            resourceEditable = context.options.eventResourceEditable;
            if (resourceEditable == null) {
                resourceEditable = context.options.editable; // TODO: use defaults system instead
            }
        }
    }
    return resourceEditable;
}
function transformEventDrop(mutation, context) {
    let { resourceMutation } = mutation;
    if (resourceMutation) {
        let { calendarApi } = context;
        return {
            oldResource: calendarApi.getResourceById(resourceMutation.matchResourceId),
            newResource: calendarApi.getResourceById(resourceMutation.setResourceId),
        };
    }
    return {
        oldResource: null,
        newResource: null,
    };
}

class ResourceDataAdder {
    constructor() {
        this.filterResources = internal_cjs.memoize(filterResources);
    }
    transform(viewProps, calendarProps) {
        if (calendarProps.viewSpec.optionDefaults.needsResourceData) {
            return {
                resourceStore: this.filterResources(calendarProps.resourceStore, calendarProps.options.filterResourcesWithEvents, calendarProps.eventStore, calendarProps.dateProfile.activeRange),
                resourceEntityExpansions: calendarProps.resourceEntityExpansions,
            };
        }
        return null;
    }
}
function filterResources(resourceStore, doFilterResourcesWithEvents, eventStore, activeRange) {
    if (doFilterResourcesWithEvents) {
        let instancesInRange = filterEventInstancesInRange(eventStore.instances, activeRange);
        let hasEvents = computeHasEvents(instancesInRange, eventStore.defs);
        Object.assign(hasEvents, computeAncestorHasEvents(hasEvents, resourceStore));
        return internal_cjs.filterHash(resourceStore, (resource, resourceId) => hasEvents[resourceId]);
    }
    return resourceStore;
}
function filterEventInstancesInRange(eventInstances, activeRange) {
    return internal_cjs.filterHash(eventInstances, (eventInstance) => internal_cjs.rangesIntersect(eventInstance.range, activeRange));
}
function computeHasEvents(eventInstances, eventDefs) {
    let hasEvents = {};
    for (let instanceId in eventInstances) {
        let instance = eventInstances[instanceId];
        for (let resourceId of eventDefs[instance.defId].resourceIds) {
            hasEvents[resourceId] = true;
        }
    }
    return hasEvents;
}
/*
mark resources as having events if any of their ancestors have them
NOTE: resourceStore might not have all the resources that hasEvents{} has keyed
*/
function computeAncestorHasEvents(hasEvents, resourceStore) {
    let res = {};
    for (let resourceId in hasEvents) {
        let resource;
        while ((resource = resourceStore[resourceId])) {
            resourceId = resource.parentId; // now functioning as the parentId
            if (resourceId) {
                res[resourceId] = true;
            }
            else {
                break;
            }
        }
    }
    return res;
}
/*
for making sure events that have editable resources are always draggable in resource views
*/
function transformIsDraggable(val, eventDef, eventUi, context) {
    if (!val) {
        let state = context.getCurrentData();
        let viewSpec = state.viewSpecs[state.currentViewType];
        if (viewSpec.optionDefaults.needsResourceData) {
            if (computeResourceEditable(eventDef, context)) {
                return true;
            }
        }
    }
    return val;
}

// for when non-resource view should be given EventUi info (for event coloring/constraints based off of resource data)
class ResourceEventConfigAdder {
    constructor() {
        this.buildResourceEventUis = internal_cjs.memoize(buildResourceEventUis, internal_cjs.isPropsEqual);
        this.injectResourceEventUis = internal_cjs.memoize(injectResourceEventUis);
    }
    transform(viewProps, calendarProps) {
        if (!calendarProps.viewSpec.optionDefaults.needsResourceData) {
            return {
                eventUiBases: this.injectResourceEventUis(viewProps.eventUiBases, viewProps.eventStore.defs, this.buildResourceEventUis(calendarProps.resourceStore)),
            };
        }
        return null;
    }
}
function buildResourceEventUis(resourceStore) {
    return internal_cjs.mapHash(resourceStore, (resource) => resource.ui);
}
function injectResourceEventUis(eventUiBases, eventDefs, resourceEventUis) {
    return internal_cjs.mapHash(eventUiBases, (eventUi, defId) => {
        if (defId) { // not the '' key
            return injectResourceEventUi(eventUi, eventDefs[defId], resourceEventUis);
        }
        return eventUi;
    });
}
function injectResourceEventUi(origEventUi, eventDef, resourceEventUis) {
    let parts = [];
    // first resource takes precedence, which fights with the ordering of combineEventUis, thus the unshifts
    for (let resourceId of eventDef.resourceIds) {
        if (resourceEventUis[resourceId]) {
            parts.unshift(resourceEventUis[resourceId]);
        }
    }
    parts.unshift(origEventUi);
    return internal_cjs.combineEventUis(parts);
}

let defs = []; // TODO: use plugin system
function registerResourceSourceDef(def) {
    defs.push(def);
}
function getResourceSourceDef(id) {
    return defs[id];
}
function getResourceSourceDefs() {
    return defs;
}

// TODO: make this a plugin-able parser
// TODO: success/failure
const RESOURCE_SOURCE_REFINERS = {
    id: String,
    // for array. TODO: move to resource-array
    resources: internal_cjs.identity,
    // for json feed. TODO: move to resource-json-feed
    url: String,
    method: String,
    startParam: String,
    endParam: String,
    timeZoneParam: String,
    extraParams: internal_cjs.identity,
};
function parseResourceSource(input) {
    let inputObj;
    if (typeof input === 'string') {
        inputObj = { url: input };
    }
    else if (typeof input === 'function' || Array.isArray(input)) {
        inputObj = { resources: input };
    }
    else if (typeof input === 'object' && input) { // non-null object
        inputObj = input;
    }
    if (inputObj) {
        let { refined, extra } = internal_cjs.refineProps(inputObj, RESOURCE_SOURCE_REFINERS);
        warnUnknownProps(extra);
        let metaRes = buildResourceSourceMeta(refined);
        if (metaRes) {
            return {
                _raw: input,
                sourceId: internal_cjs.guid(),
                sourceDefId: metaRes.sourceDefId,
                meta: metaRes.meta,
                publicId: refined.id || '',
                isFetching: false,
                latestFetchId: '',
                fetchRange: null,
            };
        }
    }
    return null;
}
function buildResourceSourceMeta(refined) {
    let defs = getResourceSourceDefs();
    for (let i = defs.length - 1; i >= 0; i -= 1) { // later-added plugins take precedence
        let def = defs[i];
        let meta = def.parseMeta(refined);
        if (meta) {
            return { meta, sourceDefId: i };
        }
    }
    return null;
}
function warnUnknownProps(props) {
    for (let propName in props) {
        console.warn(`Unknown resource prop '${propName}'`);
    }
}

function reduceResourceSource(source, action, context) {
    let { options, dateProfile } = context;
    if (!source || !action) {
        return createSource(options.initialResources || options.resources, dateProfile.activeRange, options.refetchResourcesOnNavigate, context);
    }
    switch (action.type) {
        case 'RESET_RESOURCE_SOURCE':
            return createSource(action.resourceSourceInput, dateProfile.activeRange, options.refetchResourcesOnNavigate, context);
        case 'PREV': // TODO: how do we track all actions that affect dateProfile :(
        case 'NEXT':
        case 'CHANGE_DATE':
        case 'CHANGE_VIEW_TYPE':
            return handleRangeChange(source, dateProfile.activeRange, options.refetchResourcesOnNavigate, context);
        case 'RECEIVE_RESOURCES':
        case 'RECEIVE_RESOURCE_ERROR':
            return receiveResponse(source, action.fetchId, action.fetchRange);
        case 'REFETCH_RESOURCES':
            return fetchSource(source, dateProfile.activeRange, context);
        default:
            return source;
    }
}
function createSource(input, activeRange, refetchResourcesOnNavigate, context) {
    if (input) {
        let source = parseResourceSource(input);
        source = fetchSource(source, refetchResourcesOnNavigate ? activeRange : null, context);
        return source;
    }
    return null;
}
function handleRangeChange(source, activeRange, refetchResourcesOnNavigate, context) {
    if (refetchResourcesOnNavigate &&
        !doesSourceIgnoreRange(source) &&
        (!source.fetchRange || !internal_cjs.rangesEqual(source.fetchRange, activeRange))) {
        return fetchSource(source, activeRange, context);
    }
    return source;
}
function doesSourceIgnoreRange(source) {
    return Boolean(getResourceSourceDef(source.sourceDefId).ignoreRange);
}
function fetchSource(source, fetchRange, context) {
    let sourceDef = getResourceSourceDef(source.sourceDefId);
    let fetchId = internal_cjs.guid();
    sourceDef.fetch({
        resourceSource: source,
        range: fetchRange,
        context,
    }, (res) => {
        context.dispatch({
            type: 'RECEIVE_RESOURCES',
            fetchId,
            fetchRange,
            rawResources: res.rawResources,
        });
    }, (error) => {
        context.dispatch({
            type: 'RECEIVE_RESOURCE_ERROR',
            fetchId,
            fetchRange,
            error,
        });
    });
    return Object.assign(Object.assign({}, source), { isFetching: true, latestFetchId: fetchId });
}
function receiveResponse(source, fetchId, fetchRange) {
    if (fetchId === source.latestFetchId) {
        return Object.assign(Object.assign({}, source), { isFetching: false, fetchRange });
    }
    return source;
}

function reduceResourceStore(store, action, source, context) {
    if (!store || !action) {
        return {};
    }
    switch (action.type) {
        case 'RECEIVE_RESOURCES':
            return receiveRawResources(store, action.rawResources, action.fetchId, source, context);
        case 'ADD_RESOURCE':
            return addResource(store, action.resourceHash);
        case 'REMOVE_RESOURCE':
            return removeResource(store, action.resourceId);
        case 'SET_RESOURCE_PROP':
            return setResourceProp(store, action.resourceId, action.propName, action.propValue);
        case 'SET_RESOURCE_EXTENDED_PROP':
            return setResourceExtendedProp(store, action.resourceId, action.propName, action.propValue);
        default:
            return store;
    }
}
function receiveRawResources(existingStore, inputs, fetchId, source, context) {
    if (source.latestFetchId === fetchId) {
        let nextStore = {};
        for (let input of inputs) {
            internalCommon.parseResource(input, '', nextStore, context);
        }
        return nextStore;
    }
    return existingStore;
}
function addResource(existingStore, additions) {
    // TODO: warn about duplicate IDs
    return Object.assign(Object.assign({}, existingStore), additions);
}
function removeResource(existingStore, resourceId) {
    let newStore = Object.assign({}, existingStore);
    delete newStore[resourceId];
    // promote children
    for (let childResourceId in newStore) { // a child, *maybe* but probably not
        if (newStore[childResourceId].parentId === resourceId) {
            newStore[childResourceId] = Object.assign(Object.assign({}, newStore[childResourceId]), { parentId: '' });
        }
    }
    return newStore;
}
function setResourceProp(existingStore, resourceId, name, value) {
    let existingResource = existingStore[resourceId];
    // TODO: sanitization
    if (existingResource) {
        return Object.assign(Object.assign({}, existingStore), { [resourceId]: Object.assign(Object.assign({}, existingResource), { [name]: value }) });
    }
    return existingStore;
}
function setResourceExtendedProp(existingStore, resourceId, name, value) {
    let existingResource = existingStore[resourceId];
    if (existingResource) {
        return Object.assign(Object.assign({}, existingStore), { [resourceId]: Object.assign(Object.assign({}, existingResource), { extendedProps: Object.assign(Object.assign({}, existingResource.extendedProps), { [name]: value }) }) });
    }
    return existingStore;
}

function reduceResourceEntityExpansions(expansions, action) {
    if (!expansions || !action) {
        return {};
    }
    switch (action.type) {
        case 'SET_RESOURCE_ENTITY_EXPANDED':
            return Object.assign(Object.assign({}, expansions), { [action.id]: action.isExpanded });
        default:
            return expansions;
    }
}

function reduceResources(state, action, context) {
    let resourceSource = reduceResourceSource(state && state.resourceSource, action, context);
    let resourceStore = reduceResourceStore(state && state.resourceStore, action, resourceSource, context);
    let resourceEntityExpansions = reduceResourceEntityExpansions(state && state.resourceEntityExpansions, action);
    return {
        resourceSource,
        resourceStore,
        resourceEntityExpansions,
    };
}

const EVENT_REFINERS = {
    resourceId: String,
    resourceIds: internal_cjs.identity,
    resourceEditable: Boolean,
};
function generateEventDefResourceMembers(refined) {
    return {
        resourceIds: ensureStringArray(refined.resourceIds)
            .concat(refined.resourceId ? [refined.resourceId] : []),
        resourceEditable: refined.resourceEditable,
    };
}
function ensureStringArray(items) {
    return (items || []).map((item) => String(item));
}

function transformDateSelectionJoin(hit0, hit1) {
    let resourceId0 = hit0.dateSpan.resourceId;
    let resourceId1 = hit1.dateSpan.resourceId;
    if (resourceId0 && resourceId1) {
        return { resourceId: resourceId0 };
    }
    return null;
}

internal_cjs.CalendarImpl.prototype.addResource = function (input, scrollTo = true) {
    let currentState = this.getCurrentData();
    let resourceHash;
    let resource;
    if (input instanceof internalCommon.ResourceApi) {
        resource = input._resource;
        resourceHash = { [resource.id]: resource };
    }
    else {
        resourceHash = {};
        resource = internalCommon.parseResource(input, '', resourceHash, currentState);
    }
    this.dispatch({
        type: 'ADD_RESOURCE',
        resourceHash,
    });
    if (scrollTo) {
        // TODO: wait til dispatch completes somehow
        this.trigger('_scrollRequest', { resourceId: resource.id });
    }
    let resourceApi = new internalCommon.ResourceApi(currentState, resource);
    currentState.emitter.trigger('resourceAdd', {
        resource: resourceApi,
        revert: () => {
            this.dispatch({
                type: 'REMOVE_RESOURCE',
                resourceId: resource.id,
            });
        },
    });
    return resourceApi;
};
internal_cjs.CalendarImpl.prototype.getResourceById = function (id) {
    id = String(id);
    let currentState = this.getCurrentData(); // eslint-disable-line react/no-this-in-sfc
    if (currentState.resourceStore) { // guard against calendar with no resource functionality
        let rawResource = currentState.resourceStore[id];
        if (rawResource) {
            return new internalCommon.ResourceApi(currentState, rawResource);
        }
    }
    return null;
};
internal_cjs.CalendarImpl.prototype.getResources = function () {
    let currentState = this.getCurrentData();
    let { resourceStore } = currentState;
    let resourceApis = [];
    if (resourceStore) { // guard against calendar with no resource functionality
        for (let resourceId in resourceStore) {
            resourceApis.push(new internalCommon.ResourceApi(currentState, resourceStore[resourceId]));
        }
    }
    return resourceApis;
};
internal_cjs.CalendarImpl.prototype.getTopLevelResources = function () {
    let currentState = this.getCurrentData();
    let { resourceStore } = currentState;
    let resourceApis = [];
    if (resourceStore) { // guard against calendar with no resource functionality
        for (let resourceId in resourceStore) {
            if (!resourceStore[resourceId].parentId) {
                resourceApis.push(new internalCommon.ResourceApi(currentState, resourceStore[resourceId]));
            }
        }
    }
    return resourceApis;
};
internal_cjs.CalendarImpl.prototype.refetchResources = function () {
    this.dispatch({
        type: 'REFETCH_RESOURCES',
    });
};
function transformDatePoint(dateSpan, context) {
    return dateSpan.resourceId ?
        { resource: context.calendarApi.getResourceById(dateSpan.resourceId) } :
        {};
}
function transformDateSpan(dateSpan, context) {
    return dateSpan.resourceId ?
        { resource: context.calendarApi.getResourceById(dateSpan.resourceId) } :
        {};
}

function isPropsValidWithResources(combinedProps, context) {
    let splitter = new internalCommon.ResourceSplitter();
    let sets = splitter.splitProps(Object.assign(Object.assign({}, combinedProps), { resourceStore: context.getCurrentData().resourceStore }));
    for (let resourceId in sets) {
        let props = sets[resourceId];
        // merge in event data from the non-resource segment
        if (resourceId && sets['']) { // current segment is not the non-resource one, and there IS a non-resource one
            props = Object.assign(Object.assign({}, props), { eventStore: internal_cjs.mergeEventStores(sets[''].eventStore, props.eventStore), eventUiBases: Object.assign(Object.assign({}, sets[''].eventUiBases), props.eventUiBases) });
        }
        if (!internal_cjs.isPropsValid(props, context, { resourceId }, filterConfig.bind(null, resourceId))) {
            return false;
        }
    }
    return true;
}
function filterConfig(resourceId, config) {
    return Object.assign(Object.assign({}, config), { constraints: filterConstraints(resourceId, config.constraints) });
}
function filterConstraints(resourceId, constraints) {
    return constraints.map((constraint) => {
        let defs = constraint.defs;
        if (defs) { // we are dealing with an EventStore
            // if any of the events define constraints to resources that are NOT this resource,
            // then this resource is unconditionally prohibited, which is what a `false` value does.
            for (let defId in defs) {
                let resourceIds = defs[defId].resourceIds;
                if (resourceIds.length && resourceIds.indexOf(resourceId) === -1) { // TODO: use a hash?!!! (for other reasons too)
                    return false;
                }
            }
        }
        return constraint;
    });
}

function transformExternalDef(dateSpan) {
    return dateSpan.resourceId ?
        { resourceId: dateSpan.resourceId } :
        {};
}

const optionChangeHandlers = {
    resources: handleResources,
};
function handleResources(newSourceInput, context) {
    let oldSourceInput = context.getCurrentData().resourceSource._raw;
    if (oldSourceInput !== newSourceInput) {
        context.dispatch({
            type: 'RESET_RESOURCE_SOURCE',
            resourceSourceInput: newSourceInput,
        });
    }
}

const OPTION_REFINERS = {
    initialResources: internal_cjs.identity,
    resources: internal_cjs.identity,
    eventResourceEditable: Boolean,
    refetchResourcesOnNavigate: Boolean,
    resourceOrder: internal_cjs.parseFieldSpecs,
    filterResourcesWithEvents: Boolean,
    resourceGroupField: String,
    resourceAreaWidth: internal_cjs.identity,
    resourceAreaColumns: internal_cjs.identity,
    resourcesInitiallyExpanded: Boolean,
    datesAboveResources: Boolean,
    needsResourceData: Boolean,
    resourceAreaHeaderClassNames: internal_cjs.identity,
    resourceAreaHeaderContent: internal_cjs.identity,
    resourceAreaHeaderDidMount: internal_cjs.identity,
    resourceAreaHeaderWillUnmount: internal_cjs.identity,
    resourceGroupLabelClassNames: internal_cjs.identity,
    resourceGroupLabelContent: internal_cjs.identity,
    resourceGroupLabelDidMount: internal_cjs.identity,
    resourceGroupLabelWillUnmount: internal_cjs.identity,
    resourceLabelClassNames: internal_cjs.identity,
    resourceLabelContent: internal_cjs.identity,
    resourceLabelDidMount: internal_cjs.identity,
    resourceLabelWillUnmount: internal_cjs.identity,
    resourceLaneClassNames: internal_cjs.identity,
    resourceLaneContent: internal_cjs.identity,
    resourceLaneDidMount: internal_cjs.identity,
    resourceLaneWillUnmount: internal_cjs.identity,
    resourceGroupLaneClassNames: internal_cjs.identity,
    resourceGroupLaneContent: internal_cjs.identity,
    resourceGroupLaneDidMount: internal_cjs.identity,
    resourceGroupLaneWillUnmount: internal_cjs.identity,
};
const LISTENER_REFINERS = {
    resourcesSet: internal_cjs.identity,
    resourceAdd: internal_cjs.identity,
    resourceChange: internal_cjs.identity,
    resourceRemove: internal_cjs.identity,
};

internal_cjs.EventImpl.prototype.getResources = function () {
    let { calendarApi } = this._context;
    return this._def.resourceIds.map((resourceId) => calendarApi.getResourceById(resourceId));
};
internal_cjs.EventImpl.prototype.setResources = function (resources) {
    let resourceIds = [];
    // massage resources -> resourceIds
    for (let resource of resources) {
        let resourceId = null;
        if (typeof resource === 'string') {
            resourceId = resource;
        }
        else if (typeof resource === 'number') {
            resourceId = String(resource);
        }
        else if (resource instanceof internalCommon.ResourceApi) {
            resourceId = resource.id; // guaranteed to always have an ID. hmmm
        }
        else {
            console.warn('unknown resource type: ' + resource);
        }
        if (resourceId) {
            resourceIds.push(resourceId);
        }
    }
    this.mutate({
        standardProps: {
            resourceIds,
        },
    });
};

registerResourceSourceDef({
    ignoreRange: true,
    parseMeta(refined) {
        if (Array.isArray(refined.resources)) {
            return refined.resources;
        }
        return null;
    },
    fetch(arg, successCallback) {
        successCallback({
            rawResources: arg.resourceSource.meta,
        });
    },
});

registerResourceSourceDef({
    parseMeta(refined) {
        if (typeof refined.resources === 'function') {
            return refined.resources;
        }
        return null;
    },
    fetch(arg, successCallback, errorCallback) {
        const dateEnv = arg.context.dateEnv;
        const func = arg.resourceSource.meta;
        const publicArg = arg.range ? {
            start: dateEnv.toDate(arg.range.start),
            end: dateEnv.toDate(arg.range.end),
            startStr: dateEnv.formatIso(arg.range.start),
            endStr: dateEnv.formatIso(arg.range.end),
            timeZone: dateEnv.timeZone,
        } : {};
        internal_cjs.unpromisify(func.bind(null, publicArg), (rawResources) => successCallback({ rawResources }), errorCallback);
    },
});

registerResourceSourceDef({
    parseMeta(refined) {
        if (refined.url) {
            return {
                url: refined.url,
                method: (refined.method || 'GET').toUpperCase(),
                extraParams: refined.extraParams,
            };
        }
        return null;
    },
    fetch(arg, successCallback, errorCallback) {
        const meta = arg.resourceSource.meta;
        const requestParams = buildRequestParams(meta, arg.range, arg.context);
        internal_cjs.requestJson(meta.method, meta.url, requestParams).then(([rawResources, response]) => {
            successCallback({ rawResources, response });
        }, errorCallback);
    },
});
// TODO: somehow consolidate with event json feed
function buildRequestParams(meta, range, context) {
    let { dateEnv, options } = context;
    let startParam;
    let endParam;
    let timeZoneParam;
    let customRequestParams;
    let params = {};
    if (range) {
        startParam = meta.startParam;
        if (startParam == null) {
            startParam = options.startParam;
        }
        endParam = meta.endParam;
        if (endParam == null) {
            endParam = options.endParam;
        }
        timeZoneParam = meta.timeZoneParam;
        if (timeZoneParam == null) {
            timeZoneParam = options.timeZoneParam;
        }
        params[startParam] = dateEnv.formatIso(range.start);
        params[endParam] = dateEnv.formatIso(range.end);
        if (dateEnv.timeZone !== 'local') {
            params[timeZoneParam] = dateEnv.timeZone;
        }
    }
    // retrieve any outbound GET/POST data from the options
    if (typeof meta.extraParams === 'function') {
        // supplied as a function that returns a key/value object
        customRequestParams = meta.extraParams();
    }
    else {
        // probably supplied as a straight key/value object
        customRequestParams = meta.extraParams || {};
    }
    Object.assign(params, customRequestParams);
    return params;
}

var index = index_cjs.createPlugin({
    name: '@fullcalendar/resource',
    premiumReleaseDate: '2025-04-02',
    deps: [premiumCommonPlugin__default["default"]],
    reducers: [reduceResources],
    isLoadingFuncs: [
        (state) => state.resourceSource && state.resourceSource.isFetching,
    ],
    eventRefiners: EVENT_REFINERS,
    eventDefMemberAdders: [generateEventDefResourceMembers],
    isDraggableTransformers: [transformIsDraggable],
    eventDragMutationMassagers: [massageEventDragMutation],
    eventDefMutationAppliers: [applyEventDefMutation],
    dateSelectionTransformers: [transformDateSelectionJoin],
    datePointTransforms: [transformDatePoint],
    dateSpanTransforms: [transformDateSpan],
    viewPropsTransformers: [ResourceDataAdder, ResourceEventConfigAdder],
    isPropsValid: isPropsValidWithResources,
    externalDefTransforms: [transformExternalDef],
    eventDropTransformers: [transformEventDrop],
    optionChangeHandlers,
    optionRefiners: OPTION_REFINERS,
    listenerRefiners: LISTENER_REFINERS,
    propSetHandlers: { resourceStore: internalCommon.handleResourceStore },
});

exports.ResourceApi = internalCommon.ResourceApi;
exports["default"] = index;
