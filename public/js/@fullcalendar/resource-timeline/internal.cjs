'use strict';

Object.defineProperty(exports, '__esModule', { value: true });

var internal_cjs = require('@fullcalendar/core/internal.cjs');
var preact_cjs = require('@fullcalendar/core/preact.cjs');
var internal_cjs$2 = require('@fullcalendar/timeline/internal.cjs');
var internal_cjs$1 = require('@fullcalendar/resource/internal.cjs');
var resourcePlugin = require('@fullcalendar/resource/index.cjs');
var internal_cjs$3 = require('@fullcalendar/scrollgrid/internal.cjs');

/*
Renders the DOM responsible for the subrow expander area,
as well as the space before it (used to align expanders of similar depths)
*/
function ExpanderIcon({ depth, hasChildren, isExpanded, onExpanderClick }) {
    let nodes = [];
    for (let i = 0; i < depth; i += 1) {
        nodes.push(preact_cjs.createElement("span", { className: "fc-icon" }));
    }
    let iconClassNames = ['fc-icon'];
    if (hasChildren) {
        if (isExpanded) {
            iconClassNames.push('fc-icon-minus-square');
        }
        else {
            iconClassNames.push('fc-icon-plus-square');
        }
    }
    nodes.push(preact_cjs.createElement("span", { className: 'fc-datagrid-expander' + (hasChildren ? '' : ' fc-datagrid-expander-placeholder'), onClick: onExpanderClick },
        preact_cjs.createElement("span", { className: iconClassNames.join(' ') })));
    return preact_cjs.createElement(preact_cjs.Fragment, {}, ...nodes);
}

// worth making a PureComponent? (because of innerHeight)
class SpreadsheetIndividualCell extends internal_cjs.BaseComponent {
    constructor() {
        super(...arguments);
        this.refineRenderProps = internal_cjs.memoizeObjArg(refineRenderProps);
        this.onExpanderClick = (ev) => {
            let { props } = this;
            if (props.hasChildren) {
                this.context.dispatch({
                    type: 'SET_RESOURCE_ENTITY_EXPANDED',
                    id: props.resource.id,
                    isExpanded: !props.isExpanded,
                });
            }
        };
    }
    render() {
        let { props, context } = this;
        let { colSpec } = props;
        let renderProps = this.refineRenderProps({
            resource: props.resource,
            fieldValue: props.fieldValue,
            context,
        });
        return (preact_cjs.createElement(internal_cjs.ContentContainer, { elTag: "td", elClasses: [
                'fc-datagrid-cell',
                'fc-resource',
            ], elAttrs: {
                role: 'gridcell',
                'data-resource-id': props.resource.id,
            }, renderProps: renderProps, generatorName: colSpec.isMain ? 'resourceLabelContent' : undefined, customGenerator: colSpec.cellContent, defaultGenerator: renderResourceInner, classNameGenerator: colSpec.cellClassNames, didMount: colSpec.cellDidMount, willUnmount: colSpec.cellWillUnmount }, (InnerContent) => (preact_cjs.createElement("div", { className: "fc-datagrid-cell-frame", style: { height: props.innerHeight } },
            preact_cjs.createElement("div", { className: "fc-datagrid-cell-cushion fc-scrollgrid-sync-inner" },
                colSpec.isMain && (preact_cjs.createElement(ExpanderIcon, { depth: props.depth, hasChildren: props.hasChildren, isExpanded: props.isExpanded, onExpanderClick: this.onExpanderClick })),
                preact_cjs.createElement(InnerContent, { elTag: "span", elClasses: ['fc-datagrid-cell-main'] }))))));
    }
}
function renderResourceInner(renderProps) {
    return renderProps.fieldValue || preact_cjs.createElement(preact_cjs.Fragment, null, "\u00A0");
}
function refineRenderProps(input) {
    return {
        resource: new resourcePlugin.ResourceApi(input.context, input.resource),
        fieldValue: input.fieldValue,
        view: input.context.viewApi,
    };
}

// for VERTICAL cell grouping, in spreadsheet area
class SpreadsheetGroupCell extends internal_cjs.BaseComponent {
    render() {
        let { props, context } = this;
        let { colSpec } = props;
        let renderProps = {
            groupValue: props.fieldValue,
            view: context.viewApi,
        };
        // a grouped cell. no data that is specific to this specific resource
        // `colSpec` is for the group. a GroupSpec :(
        return (preact_cjs.createElement(internal_cjs.ContentContainer, { elTag: "td", elClasses: [
                'fc-datagrid-cell',
                'fc-resource-group',
            ], elAttrs: {
                role: 'gridcell',
                rowSpan: props.rowSpan,
            }, renderProps: renderProps, generatorName: "resourceGroupLabelContent", customGenerator: colSpec.cellContent, defaultGenerator: renderGroupInner, classNameGenerator: colSpec.cellClassNames, didMount: colSpec.cellDidMount, willUnmount: colSpec.cellWillUnmount }, (InnerContent) => (preact_cjs.createElement("div", { className: "fc-datagrid-cell-frame fc-datagrid-cell-frame-liquid" },
            preact_cjs.createElement(InnerContent, { elTag: "div", elClasses: ['fc-datagrid-cell-cushion', 'fc-sticky'] })))));
    }
}
function renderGroupInner(renderProps) {
    return renderProps.groupValue || preact_cjs.createElement(preact_cjs.Fragment, null, "\u00A0");
}

class SpreadsheetRow extends internal_cjs.BaseComponent {
    render() {
        let { props } = this;
        let { resource, rowSpans, depth } = props;
        let resourceFields = internal_cjs$1.buildResourceFields(resource); // slightly inefficient. already done up the call stack
        return (preact_cjs.createElement("tr", { role: "row" }, props.colSpecs.map((colSpec, i) => {
            let rowSpan = rowSpans[i];
            if (rowSpan === 0) { // not responsible for group-based rows. VRowGroup is
                return null;
            }
            if (rowSpan == null) {
                rowSpan = 1;
            }
            let fieldValue = colSpec.field ? resourceFields[colSpec.field] :
                (resource.title || internal_cjs$1.getPublicId(resource.id));
            if (rowSpan > 1) {
                return (preact_cjs.createElement(SpreadsheetGroupCell, { key: i, colSpec: colSpec, fieldValue: fieldValue, rowSpan: rowSpan }));
            }
            return (preact_cjs.createElement(SpreadsheetIndividualCell, { key: i, colSpec: colSpec, resource: resource, fieldValue: fieldValue, depth: depth, hasChildren: props.hasChildren, isExpanded: props.isExpanded, innerHeight: props.innerHeight }));
        })));
    }
}
SpreadsheetRow.addPropsEquality({
    rowSpans: internal_cjs.isArraysEqual,
});

// for HORIZONTAL cell grouping, in spreadsheet area
class SpreadsheetGroupRow extends internal_cjs.BaseComponent {
    constructor() {
        super(...arguments);
        this.innerInnerRef = preact_cjs.createRef();
        this.onExpanderClick = () => {
            let { props } = this;
            this.context.dispatch({
                type: 'SET_RESOURCE_ENTITY_EXPANDED',
                id: props.id,
                isExpanded: !props.isExpanded,
            });
        };
    }
    render() {
        let { props, context } = this;
        let renderProps = { groupValue: props.group.value, view: context.viewApi };
        let spec = props.group.spec;
        return (preact_cjs.createElement("tr", { role: "row" },
            preact_cjs.createElement(internal_cjs.ContentContainer, { elTag: "th", elClasses: [
                    'fc-datagrid-cell',
                    'fc-resource-group',
                    context.theme.getClass('tableCellShaded'),
                ], elAttrs: {
                    // ARIA TODO: not really a columnheader
                    // extremely tedious to make this aria-compliant,
                    // to assign multiple headers to each cell
                    // https://www.w3.org/WAI/tutorials/tables/multi-level/
                    role: 'columnheader',
                    scope: 'colgroup',
                    colSpan: props.spreadsheetColCnt,
                }, renderProps: renderProps, generatorName: "resourceGroupLabelContent", customGenerator: spec.labelContent, defaultGenerator: renderCellInner, classNameGenerator: spec.labelClassNames, didMount: spec.labelDidMount, willUnmount: spec.labelWillUnmount }, (InnerContent) => (preact_cjs.createElement("div", { className: "fc-datagrid-cell-frame", style: { height: props.innerHeight } },
                preact_cjs.createElement("div", { className: "fc-datagrid-cell-cushion fc-scrollgrid-sync-inner", ref: this.innerInnerRef },
                    preact_cjs.createElement(ExpanderIcon, { depth: 0, hasChildren: true, isExpanded: props.isExpanded, onExpanderClick: this.onExpanderClick }),
                    preact_cjs.createElement(InnerContent, { elTag: "span", elClasses: ['fc-datagrid-cell-main'] })))))));
    }
}
SpreadsheetGroupRow.addPropsEquality({
    group: internal_cjs$1.isGroupsEqual,
});
function renderCellInner(renderProps) {
    return renderProps.groupValue || preact_cjs.createElement(preact_cjs.Fragment, null, "\u00A0");
}

const SPREADSHEET_COL_MIN_WIDTH = 20;
class SpreadsheetHeader extends internal_cjs.BaseComponent {
    constructor() {
        super(...arguments);
        this.resizerElRefs = new internal_cjs.RefMap(this._handleColResizerEl.bind(this));
        this.colDraggings = {};
    }
    render() {
        let { colSpecs, superHeaderRendering, rowInnerHeights } = this.props;
        let renderProps = { view: this.context.viewApi };
        let rowNodes = [];
        rowInnerHeights = rowInnerHeights.slice(); // copy, because we're gonna pop
        if (superHeaderRendering) {
            let rowInnerHeight = rowInnerHeights.shift();
            rowNodes.push(preact_cjs.createElement("tr", { key: "row-super", role: "row" },
                preact_cjs.createElement(internal_cjs.ContentContainer, { elTag: "th", elClasses: [
                        'fc-datagrid-cell',
                        'fc-datagrid-cell-super',
                    ], elAttrs: {
                        role: 'columnheader',
                        scope: 'colgroup',
                        colSpan: colSpecs.length,
                    }, renderProps: renderProps, generatorName: "resourceAreaHeaderContent", customGenerator: superHeaderRendering.headerContent, defaultGenerator: superHeaderRendering.headerDefault, classNameGenerator: superHeaderRendering.headerClassNames, didMount: superHeaderRendering.headerDidMount, willUnmount: superHeaderRendering.headerWillUnmount }, (InnerContent) => (preact_cjs.createElement("div", { className: "fc-datagrid-cell-frame", style: { height: rowInnerHeight } },
                    preact_cjs.createElement(InnerContent, { elTag: "div", elClasses: ['fc-datagrid-cell-cushion', 'fc-scrollgrid-sync-inner'] }))))));
        }
        let rowInnerHeight = rowInnerHeights.shift();
        rowNodes.push(preact_cjs.createElement("tr", { key: "row", role: "row" }, colSpecs.map((colSpec, i) => {
            let isLastCol = i === (colSpecs.length - 1);
            // need empty inner div for abs positioning for resizer
            return (preact_cjs.createElement(internal_cjs.ContentContainer, { key: i, elTag: "th", elClasses: ['fc-datagrid-cell'], elAttrs: { role: 'columnheader' }, renderProps: renderProps, generatorName: "resourceAreaHeaderContent", customGenerator: colSpec.headerContent, defaultGenerator: colSpec.headerDefault, classNameGenerator: colSpec.headerClassNames, didMount: colSpec.headerDidMount, willUnmount: colSpec.headerWillUnmount }, (InnerContent) => (preact_cjs.createElement("div", { className: "fc-datagrid-cell-frame", style: { height: rowInnerHeight } },
                preact_cjs.createElement("div", { className: "fc-datagrid-cell-cushion fc-scrollgrid-sync-inner" },
                    colSpec.isMain && (preact_cjs.createElement("span", { className: "fc-datagrid-expander fc-datagrid-expander-placeholder" },
                        preact_cjs.createElement("span", { className: "fc-icon" }))),
                    preact_cjs.createElement(InnerContent, { elTag: "span", elClasses: ['fc-datagrid-cell-main'] })),
                !isLastCol && (preact_cjs.createElement("div", { className: "fc-datagrid-cell-resizer", ref: this.resizerElRefs.createRef(i) }))))));
        })));
        return (preact_cjs.createElement(preact_cjs.Fragment, null, rowNodes));
    }
    _handleColResizerEl(resizerEl, index) {
        let { colDraggings } = this;
        if (!resizerEl) {
            let dragging = colDraggings[index];
            if (dragging) {
                dragging.destroy();
                delete colDraggings[index];
            }
        }
        else {
            let dragging = this.initColResizing(resizerEl, parseInt(index, 10));
            if (dragging) {
                colDraggings[index] = dragging;
            }
        }
    }
    initColResizing(resizerEl, index) {
        let { pluginHooks, isRtl } = this.context;
        let { onColWidthChange } = this.props;
        let ElementDraggingImpl = pluginHooks.elementDraggingImpl;
        if (ElementDraggingImpl) {
            let dragging = new ElementDraggingImpl(resizerEl);
            let startWidth; // of just the single column
            let currentWidths; // of all columns
            dragging.emitter.on('dragstart', () => {
                let allCells = internal_cjs.findElements(internal_cjs.elementClosest(resizerEl, 'tr'), 'th');
                currentWidths = allCells.map((cellEl) => (cellEl.getBoundingClientRect().width));
                startWidth = currentWidths[index];
            });
            dragging.emitter.on('dragmove', (pev) => {
                currentWidths[index] = Math.max(startWidth + pev.deltaX * (isRtl ? -1 : 1), SPREADSHEET_COL_MIN_WIDTH);
                if (onColWidthChange) {
                    onColWidthChange(currentWidths.slice()); // send a copy since currentWidths continues to be mutated
                }
            });
            dragging.setAutoScrollEnabled(false); // because gets weird with auto-scrolling time area
            return dragging;
        }
        return null;
    }
}

class ResourceTimelineLane extends internal_cjs.BaseComponent {
    constructor() {
        super(...arguments);
        this.refineRenderProps = internal_cjs.memoizeObjArg(internal_cjs$1.refineRenderProps);
        this.handleHeightChange = (innerEl, isStable) => {
            if (this.props.onHeightChange) {
                this.props.onHeightChange(
                // would want to use own <tr> ref, but not guaranteed to be ready when this fires
                internal_cjs.elementClosest(innerEl, 'tr'), isStable);
            }
        };
    }
    render() {
        let { props, context } = this;
        let { options } = context;
        let renderProps = this.refineRenderProps({ resource: props.resource, context });
        return (preact_cjs.createElement("tr", { ref: props.elRef },
            preact_cjs.createElement(internal_cjs.ContentContainer, { elTag: "td", elClasses: [
                    'fc-timeline-lane',
                    'fc-resource',
                ], elAttrs: {
                    'data-resource-id': props.resource.id,
                }, renderProps: renderProps, generatorName: "resourceLaneContent", customGenerator: options.resourceLaneContent, classNameGenerator: options.resourceLaneClassNames, didMount: options.resourceLaneDidMount, willUnmount: options.resourceLaneWillUnmount }, (InnerContent) => (preact_cjs.createElement("div", { className: "fc-timeline-lane-frame", style: { height: props.innerHeight } },
                preact_cjs.createElement(InnerContent, { elTag: "div", elClasses: ['fc-timeline-lane-misc'] }),
                preact_cjs.createElement(internal_cjs$2.TimelineLane, { dateProfile: props.dateProfile, tDateProfile: props.tDateProfile, nowDate: props.nowDate, todayRange: props.todayRange, nextDayThreshold: props.nextDayThreshold, businessHours: props.businessHours, eventStore: props.eventStore, eventUiBases: props.eventUiBases, dateSelection: props.dateSelection, eventSelection: props.eventSelection, eventDrag: props.eventDrag, eventResize: props.eventResize, timelineCoords: props.timelineCoords, onHeightChange: this.handleHeightChange, resourceId: props.resource.id })))))); // important NOT to do liquid-height. dont want to shrink height smaller than content
    }
}

/*
parallels the SpreadsheetGroupRow
*/
class DividerRow extends internal_cjs.BaseComponent {
    render() {
        let { props, context } = this;
        let { renderHooks } = props;
        let renderProps = {
            groupValue: props.groupValue,
            view: context.viewApi,
        };
        return (preact_cjs.createElement("tr", { ref: props.elRef },
            preact_cjs.createElement(internal_cjs.ContentContainer, { elTag: "td", elRef: props.elRef, elClasses: [
                    'fc-timeline-lane',
                    'fc-resource-group',
                    context.theme.getClass('tableCellShaded'),
                ], renderProps: renderProps, generatorName: "resourceGroupLaneContent", customGenerator: renderHooks.laneContent, classNameGenerator: renderHooks.laneClassNames, didMount: renderHooks.laneDidMount, willUnmount: renderHooks.laneWillUnmount }, (InnerContainer) => (preact_cjs.createElement(InnerContainer, { elTag: "div", elStyle: { height: props.innerHeight } })))));
    }
}

class ResourceTimelineLanesBody extends internal_cjs.BaseComponent {
    render() {
        let { props, context } = this;
        let { rowElRefs, innerHeights } = props;
        return (preact_cjs.createElement("tbody", null, props.rowNodes.map((node, index) => {
            if (node.group) {
                return (preact_cjs.createElement(DividerRow, { key: node.id, elRef: rowElRefs.createRef(node.id), groupValue: node.group.value, renderHooks: node.group.spec, innerHeight: innerHeights[index] || '' }));
            }
            if (node.resource) {
                let resource = node.resource;
                return (preact_cjs.createElement(ResourceTimelineLane, Object.assign({ key: node.id, elRef: rowElRefs.createRef(node.id) }, props.splitProps[resource.id], { resource: resource, dateProfile: props.dateProfile, tDateProfile: props.tDateProfile, nowDate: props.nowDate, todayRange: props.todayRange, nextDayThreshold: context.options.nextDayThreshold, businessHours: resource.businessHours || props.fallbackBusinessHours, innerHeight: innerHeights[index] || '', timelineCoords: props.slatCoords, onHeightChange: props.onRowHeightChange })));
            }
            return null;
        })));
    }
}

class ResourceTimelineLanes extends internal_cjs.BaseComponent {
    constructor() {
        super(...arguments);
        this.rootElRef = preact_cjs.createRef();
        this.rowElRefs = new internal_cjs.RefMap();
    }
    render() {
        let { props, context } = this;
        return (preact_cjs.createElement("table", { ref: this.rootElRef, "aria-hidden": true, className: 'fc-scrollgrid-sync-table ' + context.theme.getClass('table'), style: {
                minWidth: props.tableMinWidth,
                width: props.clientWidth,
                height: props.minHeight,
            } },
            preact_cjs.createElement(ResourceTimelineLanesBody, { rowElRefs: this.rowElRefs, rowNodes: props.rowNodes, dateProfile: props.dateProfile, tDateProfile: props.tDateProfile, nowDate: props.nowDate, todayRange: props.todayRange, splitProps: props.splitProps, fallbackBusinessHours: props.fallbackBusinessHours, slatCoords: props.slatCoords, innerHeights: props.innerHeights, onRowHeightChange: props.onRowHeightChange })));
    }
    componentDidMount() {
        this.updateCoords();
    }
    componentDidUpdate() {
        this.updateCoords();
    }
    componentWillUnmount() {
        if (this.props.onRowCoords) {
            this.props.onRowCoords(null);
        }
    }
    updateCoords() {
        let { props } = this;
        if (props.onRowCoords && props.clientWidth !== null) { // a populated clientWidth means sizing has stabilized
            this.props.onRowCoords(new internal_cjs.PositionCache(this.rootElRef.current, collectRowEls(this.rowElRefs.currentMap, props.rowNodes), false, true));
        }
    }
}
function collectRowEls(elMap, rowNodes) {
    return rowNodes.map((rowNode) => elMap[rowNode.id]);
}

class ResourceTimelineGrid extends internal_cjs.DateComponent {
    constructor() {
        super(...arguments);
        this.computeHasResourceBusinessHours = internal_cjs.memoize(computeHasResourceBusinessHours);
        this.resourceSplitter = new internal_cjs$1.ResourceSplitter(); // doesn't let it do businessHours tho
        this.bgSlicer = new internal_cjs$2.TimelineLaneSlicer();
        this.slatsRef = preact_cjs.createRef(); // needed for Hit creation :(
        this.state = {
            slatCoords: null,
        };
        this.handleEl = (el) => {
            if (el) {
                this.context.registerInteractiveComponent(this, { el });
            }
            else {
                this.context.unregisterInteractiveComponent(this);
            }
        };
        this.handleSlatCoords = (slatCoords) => {
            this.setState({ slatCoords });
            if (this.props.onSlatCoords) {
                this.props.onSlatCoords(slatCoords);
            }
        };
        this.handleRowCoords = (rowCoords) => {
            this.rowCoords = rowCoords;
            if (this.props.onRowCoords) {
                this.props.onRowCoords(rowCoords);
            }
        };
    }
    render() {
        let { props, state, context } = this;
        let { dateProfile, tDateProfile } = props;
        let timerUnit = internal_cjs.greatestDurationDenominator(tDateProfile.slotDuration).unit;
        let hasResourceBusinessHours = this.computeHasResourceBusinessHours(props.rowNodes);
        let splitProps = this.resourceSplitter.splitProps(props);
        let bgLaneProps = splitProps[''];
        let bgSlicedProps = this.bgSlicer.sliceProps(bgLaneProps, dateProfile, tDateProfile.isTimeScale ? null : props.nextDayThreshold, context, // wish we didn't need to pass in the rest of these args...
        dateProfile, context.dateProfileGenerator, tDateProfile, context.dateEnv);
        // WORKAROUND: make ignore slatCoords when out of sync with dateProfile
        let slatCoords = state.slatCoords && state.slatCoords.dateProfile === props.dateProfile ? state.slatCoords : null;
        return (preact_cjs.createElement("div", { ref: this.handleEl, className: [
                'fc-timeline-body',
                props.expandRows ? 'fc-timeline-body-expandrows' : '',
            ].join(' '), style: { minWidth: props.tableMinWidth } },
            preact_cjs.createElement(internal_cjs.NowTimer, { unit: timerUnit }, (nowDate, todayRange) => (preact_cjs.createElement(preact_cjs.Fragment, null,
                preact_cjs.createElement(internal_cjs$2.TimelineSlats, { ref: this.slatsRef, dateProfile: dateProfile, tDateProfile: tDateProfile, nowDate: nowDate, todayRange: todayRange, clientWidth: props.clientWidth, tableColGroupNode: props.tableColGroupNode, tableMinWidth: props.tableMinWidth, onCoords: this.handleSlatCoords, onScrollLeftRequest: props.onScrollLeftRequest }),
                preact_cjs.createElement(internal_cjs$2.TimelineLaneBg, { businessHourSegs: hasResourceBusinessHours ? null : bgSlicedProps.businessHourSegs, bgEventSegs: bgSlicedProps.bgEventSegs, timelineCoords: slatCoords, 
                    // empty array will result in unnecessary rerenders?
                    eventResizeSegs: (bgSlicedProps.eventResize ? bgSlicedProps.eventResize.segs : []), dateSelectionSegs: bgSlicedProps.dateSelectionSegs, nowDate: nowDate, todayRange: todayRange }),
                preact_cjs.createElement(ResourceTimelineLanes, { rowNodes: props.rowNodes, dateProfile: dateProfile, tDateProfile: props.tDateProfile, nowDate: nowDate, todayRange: todayRange, splitProps: splitProps, fallbackBusinessHours: hasResourceBusinessHours ? props.businessHours : null, clientWidth: props.clientWidth, minHeight: props.expandRows ? props.clientHeight : '', tableMinWidth: props.tableMinWidth, innerHeights: props.rowInnerHeights, slatCoords: slatCoords, onRowCoords: this.handleRowCoords, onRowHeightChange: props.onRowHeightChange }),
                (context.options.nowIndicator && slatCoords && slatCoords.isDateInRange(nowDate)) && (preact_cjs.createElement("div", { className: "fc-timeline-now-indicator-container" },
                    preact_cjs.createElement(internal_cjs.NowIndicatorContainer, { elClasses: ['fc-timeline-now-indicator-line'], elStyle: internal_cjs$2.coordToCss(slatCoords.dateToCoord(nowDate), context.isRtl), isAxis: false, date: nowDate }))))))));
    }
    // Hit System
    // ------------------------------------------------------------------------------------------
    queryHit(positionLeft, positionTop) {
        let rowCoords = this.rowCoords;
        let rowIndex = rowCoords.topToIndex(positionTop);
        if (rowIndex != null) {
            let resource = this.props.rowNodes[rowIndex].resource;
            if (resource) { // not a group
                let slatHit = this.slatsRef.current.positionToHit(positionLeft);
                if (slatHit) {
                    return {
                        dateProfile: this.props.dateProfile,
                        dateSpan: {
                            range: slatHit.dateSpan.range,
                            allDay: slatHit.dateSpan.allDay,
                            resourceId: resource.id,
                        },
                        rect: {
                            left: slatHit.left,
                            right: slatHit.right,
                            top: rowCoords.tops[rowIndex],
                            bottom: rowCoords.bottoms[rowIndex],
                        },
                        dayEl: slatHit.dayEl,
                        layer: 0,
                    };
                }
            }
        }
        return null;
    }
}
function computeHasResourceBusinessHours(rowNodes) {
    for (let node of rowNodes) {
        let resource = node.resource;
        if (resource && resource.businessHours) {
            return true;
        }
    }
    return false;
}

const MIN_RESOURCE_AREA_WIDTH = 30; // definitely bigger than scrollbars
// RENAME?
class ResourceTimelineViewLayout extends internal_cjs.BaseComponent {
    constructor() {
        super(...arguments);
        this.scrollGridRef = preact_cjs.createRef();
        this.timeBodyScrollerElRef = preact_cjs.createRef();
        this.spreadsheetHeaderChunkElRef = preact_cjs.createRef();
        this.rootElRef = preact_cjs.createRef();
        this.ensureScrollGridResizeId = 0;
        this.state = {
            resourceAreaWidthOverride: null,
        };
        /*
        ghetto debounce. don't race with ScrollGrid's resizing delay. solves #6140
        */
        this.ensureScrollGridResize = () => {
            if (this.ensureScrollGridResizeId) {
                clearTimeout(this.ensureScrollGridResizeId);
            }
            this.ensureScrollGridResizeId = setTimeout(() => {
                this.scrollGridRef.current.handleSizing(false);
            }, internal_cjs.config.SCROLLGRID_RESIZE_INTERVAL + 1);
        };
    }
    render() {
        let { props, state, context } = this;
        let { options } = context;
        let stickyHeaderDates = !props.forPrint && internal_cjs.getStickyHeaderDates(options);
        let stickyFooterScrollbar = !props.forPrint && internal_cjs.getStickyFooterScrollbar(options);
        let sections = [
            {
                type: 'header',
                key: 'header',
                syncRowHeights: true,
                isSticky: stickyHeaderDates,
                chunks: [
                    {
                        key: 'datagrid',
                        elRef: this.spreadsheetHeaderChunkElRef,
                        // TODO: allow the content to specify this. have general-purpose 'content' with obj with keys
                        tableClassName: 'fc-datagrid-header',
                        rowContent: props.spreadsheetHeaderRows,
                    },
                    {
                        key: 'divider',
                        outerContent: (preact_cjs.createElement("td", { role: "presentation", className: 'fc-resource-timeline-divider ' + context.theme.getClass('tableCellShaded') })),
                    },
                    {
                        key: 'timeline',
                        content: props.timeHeaderContent,
                    },
                ],
            },
            {
                type: 'body',
                key: 'body',
                syncRowHeights: true,
                liquid: true,
                expandRows: Boolean(options.expandRows),
                chunks: [
                    {
                        key: 'datagrid',
                        tableClassName: 'fc-datagrid-body',
                        rowContent: props.spreadsheetBodyRows,
                    },
                    {
                        key: 'divider',
                        outerContent: (preact_cjs.createElement("td", { role: "presentation", className: 'fc-resource-timeline-divider ' + context.theme.getClass('tableCellShaded') })),
                    },
                    {
                        key: 'timeline',
                        scrollerElRef: this.timeBodyScrollerElRef,
                        content: props.timeBodyContent,
                    },
                ],
            },
        ];
        if (stickyFooterScrollbar) {
            sections.push({
                type: 'footer',
                key: 'footer',
                isSticky: true,
                chunks: [
                    {
                        key: 'datagrid',
                        content: internal_cjs.renderScrollShim,
                    },
                    {
                        key: 'divider',
                        outerContent: (preact_cjs.createElement("td", { role: "presentation", className: 'fc-resource-timeline-divider ' + context.theme.getClass('tableCellShaded') })),
                    },
                    {
                        key: 'timeline',
                        content: internal_cjs.renderScrollShim,
                    },
                ],
            });
        }
        let resourceAreaWidth = state.resourceAreaWidthOverride != null
            ? state.resourceAreaWidthOverride
            : options.resourceAreaWidth;
        return (preact_cjs.createElement(internal_cjs$3.ScrollGrid, { ref: this.scrollGridRef, elRef: this.rootElRef, liquid: !props.isHeightAuto && !props.forPrint, forPrint: props.forPrint, collapsibleWidth: false, colGroups: [
                { cols: props.spreadsheetCols, width: resourceAreaWidth },
                { cols: [] },
                { cols: props.timeCols },
            ], sections: sections }));
    }
    forceTimeScroll(left) {
        let scrollGrid = this.scrollGridRef.current;
        scrollGrid.forceScrollLeft(2, left); // 2 = the time area
    }
    forceResourceScroll(top) {
        let scrollGrid = this.scrollGridRef.current;
        scrollGrid.forceScrollTop(1, top); // 1 = the body
    }
    getResourceScroll() {
        let timeBodyScrollerEl = this.timeBodyScrollerElRef.current;
        return timeBodyScrollerEl.scrollTop;
    }
    // Resource Area Resizing
    // ------------------------------------------------------------------------------------------
    // NOTE: a callback Ref for the resizer was firing multiple times with same elements (Preact)
    // that's why we use spreadsheetResizerElRef instead
    componentDidMount() {
        this.initSpreadsheetResizing();
    }
    componentWillUnmount() {
        this.destroySpreadsheetResizing();
    }
    initSpreadsheetResizing() {
        let { isRtl, pluginHooks } = this.context;
        let ElementDraggingImpl = pluginHooks.elementDraggingImpl;
        let spreadsheetHeadEl = this.spreadsheetHeaderChunkElRef.current;
        if (ElementDraggingImpl) {
            let rootEl = this.rootElRef.current;
            let dragging = this.spreadsheetResizerDragging = new ElementDraggingImpl(rootEl, '.fc-resource-timeline-divider');
            let dragStartWidth;
            let viewWidth;
            dragging.emitter.on('dragstart', () => {
                dragStartWidth = spreadsheetHeadEl.getBoundingClientRect().width;
                viewWidth = rootEl.getBoundingClientRect().width;
            });
            dragging.emitter.on('dragmove', (pev) => {
                let newWidth = dragStartWidth + pev.deltaX * (isRtl ? -1 : 1);
                newWidth = Math.max(newWidth, MIN_RESOURCE_AREA_WIDTH);
                newWidth = Math.min(newWidth, viewWidth - MIN_RESOURCE_AREA_WIDTH);
                // scrollgrid will ignore resize requests if there are too many :|
                this.setState({
                    resourceAreaWidthOverride: newWidth,
                }, this.ensureScrollGridResize);
            });
            dragging.setAutoScrollEnabled(false); // because gets weird with auto-scrolling time area
        }
    }
    destroySpreadsheetResizing() {
        if (this.spreadsheetResizerDragging) {
            this.spreadsheetResizerDragging.destroy();
        }
    }
}

class ResourceTimelineView extends internal_cjs.BaseComponent {
    constructor(props, context) {
        super(props, context);
        this.processColOptions = internal_cjs.memoize(processColOptions);
        this.buildTimelineDateProfile = internal_cjs.memoize(internal_cjs$2.buildTimelineDateProfile);
        this.hasNesting = internal_cjs.memoize(hasNesting);
        this.buildRowNodes = internal_cjs.memoize(internal_cjs$1.buildRowNodes);
        this.layoutRef = preact_cjs.createRef();
        this.rowNodes = [];
        this.renderedRowNodes = [];
        this.buildRowIndex = internal_cjs.memoize(buildRowIndex);
        this.handleSlatCoords = (slatCoords) => {
            this.setState({ slatCoords });
        };
        this.handleRowCoords = (rowCoords) => {
            this.rowCoords = rowCoords;
            this.scrollResponder.update(false); // TODO: could eliminate this if rowCoords lived in state
        };
        this.handleMaxCushionWidth = (slotCushionMaxWidth) => {
            this.setState({
                slotCushionMaxWidth: Math.ceil(slotCushionMaxWidth), // for less rerendering TODO: DRY
            });
        };
        // Scrolling
        // ------------------------------------------------------------------------------------------------------------------
        // this is useful for scrolling prev/next dates while resource is scrolled down
        this.handleScrollLeftRequest = (scrollLeft) => {
            let layout = this.layoutRef.current;
            layout.forceTimeScroll(scrollLeft);
        };
        this.handleScrollRequest = (request) => {
            let { rowCoords } = this;
            let layout = this.layoutRef.current;
            let rowId = request.rowId || request.resourceId;
            if (rowCoords) {
                if (rowId) {
                    let rowIdToIndex = this.buildRowIndex(this.renderedRowNodes);
                    let index = rowIdToIndex[rowId];
                    if (index != null) {
                        let scrollTop = (request.fromBottom != null ?
                            rowCoords.bottoms[index] - request.fromBottom : // pixels from bottom edge
                            rowCoords.tops[index] // just use top edge
                        );
                        layout.forceResourceScroll(scrollTop);
                    }
                }
                return true;
            }
            return null;
        };
        // Resource INDIVIDUAL-Column Area Resizing
        // ------------------------------------------------------------------------------------------
        this.handleColWidthChange = (colWidths) => {
            this.setState({
                spreadsheetColWidths: colWidths,
            });
        };
        this.state = {
            resourceAreaWidth: context.options.resourceAreaWidth,
            spreadsheetColWidths: [],
        };
    }
    render() {
        let { props, state, context } = this;
        let { options, viewSpec } = context;
        let { superHeaderRendering, groupSpecs, orderSpecs, isVGrouping, colSpecs } = this.processColOptions(context.options);
        let tDateProfile = this.buildTimelineDateProfile(props.dateProfile, context.dateEnv, options, context.dateProfileGenerator);
        let rowNodes = this.rowNodes = this.buildRowNodes(props.resourceStore, groupSpecs, orderSpecs, isVGrouping, props.resourceEntityExpansions, options.resourcesInitiallyExpanded);
        let { slotMinWidth } = options;
        let slatCols = internal_cjs$2.buildSlatCols(tDateProfile, slotMinWidth || this.computeFallbackSlotMinWidth(tDateProfile));
        return (preact_cjs.createElement(internal_cjs.ViewContainer, { elClasses: [
                'fc-resource-timeline',
                !this.hasNesting(rowNodes) && 'fc-resource-timeline-flat',
                'fc-timeline',
                options.eventOverlap === false ?
                    'fc-timeline-overlap-disabled' :
                    'fc-timeline-overlap-enabled',
            ], viewSpec: viewSpec },
            preact_cjs.createElement(ResourceTimelineViewLayout, { ref: this.layoutRef, forPrint: props.forPrint, isHeightAuto: props.isHeightAuto, spreadsheetCols: buildSpreadsheetCols(colSpecs, state.spreadsheetColWidths, ''), spreadsheetHeaderRows: (contentArg) => (preact_cjs.createElement(SpreadsheetHeader // TODO: rename to SpreadsheetHeaderRows
                , { superHeaderRendering: superHeaderRendering, colSpecs: colSpecs, onColWidthChange: this.handleColWidthChange, rowInnerHeights: contentArg.rowSyncHeights })), spreadsheetBodyRows: (contentArg) => (preact_cjs.createElement(preact_cjs.Fragment, null, this.renderSpreadsheetRows(rowNodes, colSpecs, contentArg.rowSyncHeights))), timeCols: slatCols, timeHeaderContent: (contentArg) => (preact_cjs.createElement(internal_cjs$2.TimelineHeader, { clientWidth: contentArg.clientWidth, clientHeight: contentArg.clientHeight, tableMinWidth: contentArg.tableMinWidth, tableColGroupNode: contentArg.tableColGroupNode, dateProfile: props.dateProfile, tDateProfile: tDateProfile, slatCoords: state.slatCoords, rowInnerHeights: contentArg.rowSyncHeights, onMaxCushionWidth: slotMinWidth ? null : this.handleMaxCushionWidth })), timeBodyContent: (contentArg) => (preact_cjs.createElement(ResourceTimelineGrid, { dateProfile: props.dateProfile, clientWidth: contentArg.clientWidth, clientHeight: contentArg.clientHeight, tableMinWidth: contentArg.tableMinWidth, tableColGroupNode: contentArg.tableColGroupNode, expandRows: contentArg.expandRows, tDateProfile: tDateProfile, rowNodes: rowNodes, businessHours: props.businessHours, dateSelection: props.dateSelection, eventStore: props.eventStore, eventUiBases: props.eventUiBases, eventSelection: props.eventSelection, eventDrag: props.eventDrag, eventResize: props.eventResize, resourceStore: props.resourceStore, nextDayThreshold: context.options.nextDayThreshold, rowInnerHeights: contentArg.rowSyncHeights, onSlatCoords: this.handleSlatCoords, onRowCoords: this.handleRowCoords, onScrollLeftRequest: this.handleScrollLeftRequest, onRowHeightChange: contentArg.reportRowHeightChange })) })));
    }
    renderSpreadsheetRows(nodes, colSpecs, rowSyncHeights) {
        return nodes.map((node, index) => {
            if (node.group) {
                return (preact_cjs.createElement(SpreadsheetGroupRow, { key: node.id, id: node.id, spreadsheetColCnt: colSpecs.length, isExpanded: node.isExpanded, group: node.group, innerHeight: rowSyncHeights[index] || '' }));
            }
            if (node.resource) {
                return (preact_cjs.createElement(SpreadsheetRow, { key: node.id, colSpecs: colSpecs, rowSpans: node.rowSpans, depth: node.depth, isExpanded: node.isExpanded, hasChildren: node.hasChildren, resource: node.resource, innerHeight: rowSyncHeights[index] || '' }));
            }
            return null;
        });
    }
    componentDidMount() {
        this.renderedRowNodes = this.rowNodes;
        this.scrollResponder = this.context.createScrollResponder(this.handleScrollRequest);
    }
    getSnapshotBeforeUpdate() {
        if (!this.props.forPrint) { // because print-view is always zero?
            return { resourceScroll: this.queryResourceScroll() };
        }
        return {};
    }
    componentDidUpdate(prevProps, prevState, snapshot) {
        this.renderedRowNodes = this.rowNodes;
        this.scrollResponder.update(prevProps.dateProfile !== this.props.dateProfile);
        if (snapshot.resourceScroll) {
            this.handleScrollRequest(snapshot.resourceScroll); // TODO: this gets triggered too often
        }
    }
    componentWillUnmount() {
        this.scrollResponder.detach();
    }
    computeFallbackSlotMinWidth(tDateProfile) {
        return Math.max(30, ((this.state.slotCushionMaxWidth || 0) / tDateProfile.slotsPerLabel));
    }
    queryResourceScroll() {
        let { rowCoords, renderedRowNodes } = this;
        if (rowCoords) {
            let layout = this.layoutRef.current;
            let trBottoms = rowCoords.bottoms;
            let scrollTop = layout.getResourceScroll();
            let scroll = {};
            for (let i = 0; i < trBottoms.length; i += 1) {
                let rowNode = renderedRowNodes[i];
                let elBottom = trBottoms[i] - scrollTop; // from the top of the scroller
                if (elBottom > 0) {
                    scroll.rowId = rowNode.id;
                    scroll.fromBottom = elBottom;
                    break;
                }
            }
            return scroll;
        }
        return null;
    }
}
ResourceTimelineView.addStateEquality({
    spreadsheetColWidths: internal_cjs.isArraysEqual,
});
function buildRowIndex(rowNodes) {
    let rowIdToIndex = {};
    for (let i = 0; i < rowNodes.length; i += 1) {
        rowIdToIndex[rowNodes[i].id] = i;
    }
    return rowIdToIndex;
}
function buildSpreadsheetCols(colSpecs, forcedWidths, fallbackWidth = '') {
    return colSpecs.map((colSpec, i) => ({
        className: colSpec.isMain ? 'fc-main-col' : '',
        width: forcedWidths[i] || colSpec.width || fallbackWidth,
    }));
}
function hasNesting(nodes) {
    for (let node of nodes) {
        if (node.group) {
            return true;
        }
        if (node.resource) {
            if (node.hasChildren) {
                return true;
            }
        }
    }
    return false;
}
function processColOptions(options) {
    let allColSpecs = options.resourceAreaColumns || [];
    let superHeaderRendering = null;
    if (!allColSpecs.length) {
        allColSpecs.push({
            headerClassNames: options.resourceAreaHeaderClassNames,
            headerContent: options.resourceAreaHeaderContent,
            headerDefault: () => 'Resources',
            headerDidMount: options.resourceAreaHeaderDidMount,
            headerWillUnmount: options.resourceAreaHeaderWillUnmount,
        });
    }
    else if (options.resourceAreaHeaderContent) { // weird way to determine if content
        superHeaderRendering = {
            headerClassNames: options.resourceAreaHeaderClassNames,
            headerContent: options.resourceAreaHeaderContent,
            headerDidMount: options.resourceAreaHeaderDidMount,
            headerWillUnmount: options.resourceAreaHeaderWillUnmount,
        };
    }
    let plainColSpecs = [];
    let groupColSpecs = []; // part of the colSpecs, but filtered out in order to put first
    let groupSpecs = [];
    let isVGrouping = false;
    for (let colSpec of allColSpecs) {
        if (colSpec.group) {
            groupColSpecs.push(Object.assign(Object.assign({}, colSpec), { cellClassNames: colSpec.cellClassNames || options.resourceGroupLabelClassNames, cellContent: colSpec.cellContent || options.resourceGroupLabelContent, cellDidMount: colSpec.cellDidMount || options.resourceGroupLabelDidMount, cellWillUnmount: colSpec.cellWillUnmount || options.resourceGroupLaneWillUnmount }));
        }
        else {
            plainColSpecs.push(colSpec);
        }
    }
    // BAD: mutates a user-supplied option
    let mainColSpec = plainColSpecs[0];
    mainColSpec.isMain = true;
    mainColSpec.cellClassNames = mainColSpec.cellClassNames || options.resourceLabelClassNames;
    mainColSpec.cellContent = mainColSpec.cellContent || options.resourceLabelContent;
    mainColSpec.cellDidMount = mainColSpec.cellDidMount || options.resourceLabelDidMount;
    mainColSpec.cellWillUnmount = mainColSpec.cellWillUnmount || options.resourceLabelWillUnmount;
    if (groupColSpecs.length) {
        groupSpecs = groupColSpecs;
        isVGrouping = true;
    }
    else {
        let hGroupField = options.resourceGroupField;
        if (hGroupField) {
            groupSpecs.push({
                field: hGroupField,
                labelClassNames: options.resourceGroupLabelClassNames,
                labelContent: options.resourceGroupLabelContent,
                labelDidMount: options.resourceGroupLabelDidMount,
                labelWillUnmount: options.resourceGroupLabelWillUnmount,
                laneClassNames: options.resourceGroupLaneClassNames,
                laneContent: options.resourceGroupLaneContent,
                laneDidMount: options.resourceGroupLaneDidMount,
                laneWillUnmount: options.resourceGroupLaneWillUnmount,
            });
        }
    }
    let allOrderSpecs = options.resourceOrder || internal_cjs$1.DEFAULT_RESOURCE_ORDER;
    let plainOrderSpecs = [];
    for (let orderSpec of allOrderSpecs) {
        let isGroup = false;
        for (let groupSpec of groupSpecs) {
            if (groupSpec.field === orderSpec.field) {
                groupSpec.order = orderSpec.order; // -1, 0, 1
                isGroup = true;
                break;
            }
        }
        if (!isGroup) {
            plainOrderSpecs.push(orderSpec);
        }
    }
    return {
        superHeaderRendering,
        isVGrouping,
        groupSpecs,
        colSpecs: groupColSpecs.concat(plainColSpecs),
        orderSpecs: plainOrderSpecs,
    };
}

var css_248z = ".fc .fc-resource-timeline-divider{cursor:col-resize;width:3px}.fc .fc-resource-group{font-weight:inherit;text-align:inherit}.fc .fc-resource-timeline .fc-resource-group:not([rowspan]){background:var(--fc-neutral-bg-color)}.fc .fc-timeline-lane-frame{position:relative}.fc .fc-timeline-overlap-enabled .fc-timeline-lane-frame .fc-timeline-events{box-sizing:content-box;padding-bottom:10px}.fc-timeline-body-expandrows td.fc-timeline-lane{position:relative}.fc-timeline-body-expandrows .fc-timeline-lane-frame{position:static}.fc-datagrid-cell-frame-liquid{height:100%}.fc-liquid-hack .fc-datagrid-cell-frame-liquid{bottom:0;height:auto;left:0;position:absolute;right:0;top:0}.fc .fc-datagrid-header .fc-datagrid-cell-frame{align-items:center;display:flex;justify-content:flex-start;position:relative}.fc .fc-datagrid-cell-resizer{bottom:0;cursor:col-resize;position:absolute;top:0;width:5px;z-index:1}.fc .fc-datagrid-cell-cushion{overflow:hidden;padding:8px;white-space:nowrap}.fc .fc-datagrid-expander{cursor:pointer;opacity:.65}.fc .fc-datagrid-expander .fc-icon{display:inline-block;width:1em}.fc .fc-datagrid-expander-placeholder{cursor:auto}.fc .fc-resource-timeline-flat .fc-datagrid-expander-placeholder{display:none}.fc-direction-ltr .fc-datagrid-cell-resizer{right:-3px}.fc-direction-rtl .fc-datagrid-cell-resizer{left:-3px}.fc-direction-ltr .fc-datagrid-expander{margin-right:3px}.fc-direction-rtl .fc-datagrid-expander{margin-left:3px}";
internal_cjs.injectStyles(css_248z);

exports.ResourceTimelineLane = ResourceTimelineLane;
exports.ResourceTimelineView = ResourceTimelineView;
exports.SpreadsheetRow = SpreadsheetRow;
