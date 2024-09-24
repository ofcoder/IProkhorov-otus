console.log('coursePopUp.js');

let alreadySelectedRights = {};
let markedElements = {};
const existRightColor = '#aeae';

function getAlreadySelectedRight() {
    let result = {};

    const selectedRights = BX.findChild(BX('LESSON_RIGHTS_table'), {tag: "input", attribute: {name: "LESSON_RIGHTS[][GROUP_CODE]"}}, true, true);
    selectedRights.forEach(function(element) {
        const parentCode = element.value.split(['_'])[0];
        if (!result.hasOwnProperty(parentCode)) {
            result[parentCode] = [];
        }

        if (parentCode !== element.value)  {
            result[parentCode].push(element.value);
        }
    });

    return result;
}

function isRightExists(id) {
    const parentCode = id.split(['_'])[0];

    return (
        alreadySelectedRights.hasOwnProperty(parentCode)
        && (
            id === parentCode
            || BX.util.in_array(id, alreadySelectedRights[parentCode])
        )
    );
}

function markAsExistsRight(element, elementId, color) {
    element.style.backgroundColor = color;

    if (!markedElements.hasOwnProperty(elementId)) {
        markedElements[elementId] = element;
    }

    if (elementId.split('_').length > 1 || alreadySelectedRights[elementId].length == 0) {
        element.style.pointerEvents = "none";
        if (element.getAttribute('onclick') != '')
        {
            element.setAttribute('proxy_onclick', element.getAttribute('onclick'));
            element.setAttribute('onclick', '');
        }
    }
}


function unMarkAsExistsRight(element, elementId) {
    element.style.removeProperty('background-color');
    element.style.removeProperty('pointer-events');

    if (markedElements.hasOwnProperty(elementId)) {
        delete markedElements[elementId];
    }

    if (element.getAttribute('proxy_onclick') != null && element.getAttribute('proxy_onclick') != '')
    {
        element.setAttribute('onclick', element.getAttribute('proxy_onclick'));
        element.setAttribute('proxy_onclick', '');
    }
}

function updateRenderedRightList() {
    for (let key in markedElements) {
        const parentCode = key.split('_')[0];

        if ( alreadySelectedRights.hasOwnProperty(parentCode) ) {
            if (parentCode === key || alreadySelectedRights[parentCode].includes(key)) {
                continue;
            }
        }

        unMarkAsExistsRight(markedElements[key], key);
    }
}

BX.Finder.onDisableItem = function(mapId) {
    element = BX.Finder.elements[mapId];
    elementId = BX.Finder.mapElements[mapId];

    if (isRightExists(elementId)) {
        markAsExistsRight(element, elementId, existRightColor);
    }

    if (BX.Finder.context == 'access' && BX.Access.obAlreadySelected[elementId])
    {
        if (BX.Access.showSelected)
        {
            BX.addClass(element, 'bx-finder-box-item-selected');
            if (!BX.Finder.selectedElement[elementId])
                BX.Finder.selectedElement[elementId] = [];

            BX.Finder.selectedElement[elementId].push(element);
        }
        else if (BX.util.array_search(element, BX.Finder.disabledElement) == -1 || isRightExists(elementId))
        {
            //BX.addClass(element, 'bx-finder-element-disabled');
            if (element.getAttribute('onclick') != '')
            {
                element.setAttribute('proxy_onclick', element.getAttribute('onclick'));
                element.setAttribute('onclick', '');
            }
            BX.Finder.disabledId.push(elementId);
            BX.Finder.disabledElement.push(element);
        }
    }
};

BX.ready(function() {
    BX.addCustomEvent(BX.Access, "onAfterPopupShow", function() {
        alreadySelectedRights = getAlreadySelectedRight();
        updateRenderedRightList();
    });
});
