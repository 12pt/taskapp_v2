"use strict";

// global object to hold all the tasks. Used primarily for the edit dialog to allow
// for easy editing of specific tasks.
var TASKS; 

/**
 * Search an array of task objects for a certain ID.
 * @param {Object} tasks the tasks object.
 * @param {id} string the unique task ID.
 */
function findTask(tasks, id) {
    if(id > 0 && tasks.length > 0) {
        for(var task of tasks) {
            if(task["id"] == id) {
                return task;
            }
        }
    }
    return null;
}

/**
 * Delete a single node by its ID. Or at least, request we do.
 * @param {string} id the id of the task.
 * @param {Event} the event that triggered this (clicking on THIS task).
 */
function deleteTask(id, event) {
    ajax.del("http://localhost:8000/api.php/" + id, {}, function(response) {
        if(response.error) {
            error("Couldn't delete the task with id " + id, true);
        } else {
            event.target.parentNode.parentNode.removeChild(event.target.parentNode);
            console.log("Removed task " + id);
        }
    });
}

/**
 * Edit a single task by its id.
 * @param {string} id the id of the task
 * @param {Node} input the input field holding the title
 * @param {Node} input the input field holding the content
 */
function editTask(id, titleInput, contentInput) {
    ajax.put("http://localhost:8000/api.php/" + id,
             {"title": titleInput.value, "content": contentInput.value},
             function(response) {
                 if(!response.error) {
                     var taskContainer = document.querySelector("[data-id=\"" + id + "\"]").parentNode;
                     taskContainer.querySelector("h2").innerText = titleInput.value;
                     taskContainer.querySelector("p").innerText = contentInput.value;

                     titleInput.value = "New Task";
                     contentInput.value = "";
                 }
             }, false);
}

/**
 * Called when the Add button is pressed, adds a task with the given information in the input
 * fields of the form.
 * @param {Event} event the event that triggered the addition of the task (click event).
 */
function addTask(event) {
    var form = document.querySelector("#input");
    var iTitle = form.querySelector("#titleInput");
    var iContent = form.querySelector("#contentInput");

    ajax.post("http://localhost:8000/api.php/", {title: iTitle.value, content: iContent.value}, function(response) {
        if(response.error) {
            error("Error when trying to post a new task: " + response, true);
        } else {
            iTitle.value = "New Task";
            iContent.value = "";

            var insertArea = document.querySelector("#tasklist");
            insertArea.appendChild(makeTaskElement(JSON.parse(response)));
        }
    }, false);
}

/**
 * Convert the task-add-form to a task-edit-form.
 * @param {string} id the id of the task.
 */
function showEditDialog(id) {
    // convert the task submission form to edit form
    var form = document.querySelector("#input");
    var saveButton = form.querySelector("#magicButton");
    form.removeChild(saveButton);

    var edit = document.createElement("input");
    edit.type = "button";
    edit.setAttribute("value", "Save");
    form.appendChild(edit);

    var task = findTask(TASKS, id);
    var iTitle = form.querySelector("#titleInput");
    var iContent = form.querySelector("#contentInput");

    iTitle.value = task["title"];
    iContent.value = task["content"];
    iContent.placeholder = "Edit your task description here";

    edit.addEventListener("click", function() {
        editTask(id, iTitle, iContent);
        form.removeChild(edit);
        form.appendChild(saveButton);
    });
}

/**
 * Wrap all the information for a single task JSON object in HTML.
 * @param {Object} task an object containing the task.
 */
function makeTaskElement(task) {
    var container = document.createElement("div");
    container.className = "task";
    var title = document.createElement("h2");
    var content = document.createElement("p");
    var creationTime = document.createElement("p");

    var delButton = document.createElement("input");
    delButton.setAttribute("value", "delete");
    delButton.setAttribute("data-id", task["id"]);
    delButton.type = "button";

    delButton.addEventListener("click", function(event) {
        var id = event.target.getAttribute("data-id");
        deleteTask(id, event);
    }, false);

    var editButton = document.createElement("input");
    editButton.className = "editButton";
    editButton.setAttribute("value", "edit");
    editButton.setAttribute("data-id", task["id"]);
    editButton.type = "button";

    editButton.addEventListener("click", function(event) {
        var id = event.target.getAttribute("data-id");
        showEditDialog(id);
    });

    container.appendChild(title);
    container.appendChild(content);
    container.appendChild(creationTime);
    container.appendChild(delButton);
    container.appendChild(editButton);

    title.innerText = task["title"];
    content.innerText = task["content"];
    creationTime.innerText = task["date_created"];

    return container;
}

/**
 * Utility function to remove all children of a node, used to clear the tasklist
 * between refreshes. (refreshing not yet implemented).
 * @param {Node} node the node whose children should be deleted.
 */
function deleteChildren(node) {
    while(node.firstChild) {
        node.removeChild(node.firstChild);
    }
}

/**
 * Called once on page load, written so it could be called periodically.
 * Makes a GET request to the API to get all tasks, and then it formats them in HTML
 * and gently places them in the #tasklist div.
 */
function loadTasks() {
    ajax.get("http://localhost:8000/api.php/", {}, function(data) {
        data = JSON.parse(data);

        if(!data.error) {
            hideError();

            var insertArea = document.querySelector("#tasklist");
            deleteChildren(insertArea);
            TASKS = data;
            if(TASKS.length > 0) {
                for(var task of TASKS) {
                    var container = makeTaskElement(task);
                    insertArea.appendChild(container);
                }
            } else {
                error("No tasks planned yet.");
            }
        } else {
            // turn on the warning div
            if(warning) {
                error("An error occured when trying to load your tasks. Are you connected to the database?", false);
            }
        }
    }, false);
}

document.addEventListener("DOMContentLoaded", function() {
    var nojs = document.querySelector("#nojs");
    if(nojs.style == null) {
        nojs.style = {};
    }
    nojs.style.visibility = "hidden";

    loadTasks();
    document.querySelector("#magicButton").addEventListener("click", addTask);
}, false);
