require([
        'jquery',
        'ko',
        'uiRegistry',
        'ace'
    ],
    function ($, ko, registry) {
        window.registry = registry;
        window.$ = $;

        let targetNode = document.body;

        // Configuración del observer
        let config = {childList: true, subtree: true};

        // Callback que se ejecuta cuando se detecta un cambio
        let callback = function (mutationsList) {
            for (let mutation of mutationsList) {
                if (mutation.type === "childList") {
                    $(mutation.addedNodes).each(function () {
                        if ($(this).is("input[name=\"fields[content]\"]")) {
                            observer.disconnect();
                            const container = $('<div id="ace-container-text-editor" style="min-height: 200px; width: 100%"></div>');
                            $('.entry-edit').append(container);

                            let result = [];

                            const editor = ace.edit(container[0]);
                            editor.session.setMode("ace/mode/php");
                            editor.session.on('change', function () {
                                const value = editor.getValue();
                                registry.get('southbay_ui_test.areas.fields.fields.content').value(value);
                                $($('.content-text-area textarea')[0]).text(value);
                            });

                            let value = registry.get('southbay_ui_test.areas.fields.fields.content').value();

                            if (value) {
                                const _result = registry.get('southbay_ui_test.form_data_source').data.fields.result;
                                if (_result && _result.length > 0) {
                                    result = _result;
                                }
                            } else {
                                value =
                                    "<?php\n" +
                                    "//Add out message: $out('message'); or $out('message',['a'=> 1, 'b'=> 2]);\n" +
                                    "//$collection = $objectManager->get('Magento\\Sales\\Model\\ResourceModel\\Order\\Collection');\n" +
                                    "$objectManager = \\Magento\\Framework\\App\\ObjectManager::getInstance();\n" +
                                    "$resource = $objectManager->get('Magento\\Framework\\App\\ResourceConnection');\n" +
                                    "$connection = $resource->getConnection();";
                            }

                            editor.setValue(value, 1);

                            if (result.length > 0) {
                                const containerViewer = $('<div id="ace-container-text-view" style="min-height: 200px; width: 100%"></div>');
                                $('.entry-edit').append(containerViewer);

                                const out = ace.edit(containerViewer[0]);
                                out.session.setMode("ace/mode/json");
                                out.setReadOnly(true);
                                out.setTheme("ace/theme/dracula");
                                out.setValue(JSON.stringify({out: result}, null, 2), -1);
                            }
                        }
                    });
                }
            }
        };

        // Crear una instancia del MutationObserver y comenzar a observar
        let observer = new MutationObserver(callback);
        observer.observe(targetNode, config);

        function loadResult(result) {

            result.forEach(function (element) {

            });
        }
    }
);
