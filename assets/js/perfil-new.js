
const actividad = {
    total: 0,
    show: 25,
    cargar: (id, ac_do) => {
        // Renombrar 'ac_do' a 'action' para claridad
        const action = ac_do; 
        
        $('#last-activity-view-more').remove();
        
        if (action === 'filtrar') {
            actividad.total = 0; // Resetear total al filtrar
        }

        const sendObj = { pid: MURO_CONFIG.PID, ac_do: action, do: action, start: actividad.total };
        
        loading.start(); // Iniciar loader aquí para cubrir toda la petición
        $.post(`/perfil-actividad.php`, sendObj)
            .done(res => {
                const { status, message } = handleServerResponse(res, 'Error en actividad');
                if (status === 1) {
                    // Usar la ternaria para seleccionar el método DOM (más limpio)
                    const typeAttr = (action === 'more') ? 'append' : 'html';
                    $('#last-activity-container')[typeAttr](message);
                    
                    // 💡 OPTIMIZACIÓN: El total no debería leerse del DOM.
                    // Asumimos que el HTML devuelto tiene el nuevo total
                    // en un atributo o variable. Si se usa el total del DOM,
                    // al menos lo hacemos una vez antes de removerlo.
                    const total_pubs = parseInt($('#total_acts').attr('val') || '0', 10);
                    actividad.total += total_pubs;
                    $('#total_acts').remove(); 
                }
            })
            .always();
    },
    borrar: (acid, obj) => {
        loading.start();
        $.post(`/perfil-actividad.php`, { pid: MURO_CONFIG.PID, acid, do: 'borrar' })
            .done(res => {
                const { status } = handleServerResponse(res, 'Error al borrar actividad');
                if (status === 1) {
                    // Simplificación de la navegación DOM
                    $(obj).closest('.activity-item').remove(); 
                }
            })
            .always(() => loading.end());
    }
};

// =========================================================
// GESTIÓN DEL MURO (Muro Stream)
// =========================================================

const muro = {
    maxWidth: 463,
    caracteres: MURO_CONFIG.CHAR_LIMIT,
    placeholder: {
        foto: basePath + '/files/images/imagen_' + generateRandomString(3) + '.png',
        enlace: basePath + '/blog/' + generateRandomString(4) + '/ejemplo.html',
        video: 'https://www.youtube.com/watch?v=' + generateRandomString(11)
    },
    extensiones: ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'jfif'],
    stream: {
        total: 0, 
        show: MURO_CONFIG.DEFAULT_LOAD,
        type: 'status',
        status: 0, // 🚨 Flag de estado: se recomienda usar 'disabled' en el botón.
        adjunto: '',

        // Helper para gestionar el estado de carga (Lleva el status y el loader visual)
        setLoaderStatus: (active, text = 'Publicando') => {
            muro.stream.status = active ? 1 : 0;
            if (active) {
                muro.stream.loader(true, text);
            } else {
                muro.stream.loader(false);
            }
        },

        load: (aid, obj) => {
            muro.stream.type = aid;
            const $inputAppend = $(".input-append");
            let formHtml = '';

            // Lógica de Renderizado usando Template Literals
            if (aid !== 'stream' && aid !== 'foto') {
                const placeholder = muro.placeholder[aid];
                formHtml = `<div class="frame-input-group my-3 d-flex justify-content-start align-items-center">
                    <input class="frame-input rounded px-2 flex-grow-1 h-100" type="text" name="i${aid}" placeholder="${placeholder}">
                    <div class="frame-input-button rounded px-3 h-100" role="button" onclick="muro.stream.adjuntar()">Adjuntar</div>
                </div>`;
            } else if (aid === 'foto') {
                formHtml = `<div class="frame-file-group my-3">
                    <input class="frame-input" type="file" name="i${aid}" accept="image/*">
                    <div id="progress"></div><div id="preview"></div>
                </div>`;
                // Adjuntar listener de cambio
                // 💡 Se recomienda mover este listener a la función init/ready
                $('input[name="ifoto"]').on('change', function() {
                    muro.stream.type = 'foto';
                    loading.start();
                    importModule('ImageUpload.js', 'handleImageUploadFn', this.files[0]);
                });
            } else {
                $('.shout__buttons').hide();
                muro.stream.type = 'status';
            }

            // Inserción única y limpieza
            $inputAppend.html(formHtml);
            $('.shout__pub > div').removeClass('active');
            $(obj).addClass('active');
            $('#attaContent > div').hide();
            $('#' + aid + 'Frame').show();
            
            return false;
        },

        adjuntar: () => {
            if (muro.stream.status === 1) return false;
            muro.stream.setLoaderStatus(true, 'Adjuntando');

            const $input = $(`input[name=i${muro.stream.type}]`);
            const valid = muro.stream.validar($input);

            if (valid === true) {
                $input.attr('disabled', 'true');
                muro.stream.ajaxCheck($input.val(), $input);
            } else {
                UPModal.alert({ title: 'Error al publicar', body: valid });
                $input.removeAttr('disabled').val('');
                muro.stream.setLoaderStatus(false);
            }
        },

        ajaxCheck: (url, $input) => {
            $.post(`/muro-stream.php?do=check&type=${muro.stream.type}`, { url })
                .done(res => {
                    const { status, message } = handleServerResponse(res, 'Error al publicar');
                    
                    if (status === 1) {
                        muro.stream.adjunto = $input.val();
                        $('.input-append').html(message);
                        $('.shout__buttons').hide();
                    } else {
                        $input.removeAttr('disabled'); // Habilitar si hay error
                    }
                })
                .always(() => {
                    muro.stream.setLoaderStatus(false);
                });
        },

        validar: ($input) => {
            const val = $input.val();
            const regex = /^(ht|f)tps?:\/\/\w+([\.\-\w]+)?\.([a-z]{2,3}|info|mobi|aero|asia|name)(:\d{2,5})?(\/)?((\/).+)?$/i;
            
            if (empty(val) || regex.test(val) === false) {
                return 'Debes ingresar una direcci&oacute;n URL v&aacute;lida.';
            }

            // Validación específica de tipo (usamos switch más limpio)
            switch (muro.stream.type) {
                case 'foto':
                    $input.val(val.replace(' ', ''));
                    const ext_img = val.slice((val.lastIndexOf(".") - 1 >>> 0) + 2);
                    if (!muro.extensiones.includes(ext_img.toLowerCase())) {
                        return 'S&oacute;lo se permiten im&aacute;genes .jpg, .jpeg, .png, .gif, .webp, .svg y .jfif';
                    }
                    break;
                case 'video':
                    // 💡 IMPORTANTE: 'getYoutubeId' no está definido en el código, se asume que existe
                    if (typeof getYoutubeId === 'function' && getYoutubeId(val) === false) {
                        return 'Al parecer la url del video no es v&aacute;lida. Recuerda que solo puedes compartir videos de YouTube.';
                    }
                    break;
            }
            
            return true;
        },

        compartir: () => {
            if (muro.stream.status === 1) return false;
            muro.stream.setLoaderStatus(true);
            
            const $wall = $('#wall');
            const content = $wall.val();
            const contentTrimmed = content.trim();
            const errorLengthMsg = `Las publicaciones deben ser inferiores a ${MURO_CONFIG.CHAR_LIMIT} caracteres. Ya has ingresado ${content.length} caracteres.`;
            let errorMsg = false;
            
            // 1. VALIDACIÓN GENERAL DE LONGITUD
            if (content.length > MURO_CONFIG.CHAR_LIMIT) {
                errorMsg = errorLengthMsg;
            }
            
            // 2. VALIDACIÓN DE CONTENIDO Y ADJUNTOS
            if (muro.stream.type !== 'status') {
                if (muro.stream.adjunto === '') {
                    errorMsg = 'Ingresa la <strong>URL</strong> en el campo de texto y a continuaci&oacute;n da clic en <strong>Adjuntar</strong>.';
                } else if (!errorMsg) {
                    // Si hay adjunto y no hay error de longitud, procedemos.
                    muro.stream.ajaxPost(contentTrimmed);
                    return;
                }
            } else if (muro.stream.type === 'status') {
                if (empty(contentTrimmed)) {
                    $wall.blur();
                    errorMsg = 'El estado no puede estar vacío.';
                }
                if (!errorMsg) {
                    // Si es status simple y es válido, procedemos.
                    muro.stream.ajaxPost(content);
                    return;
                }
            }
            
            // 3. Manejo de Errores (Si el código llega aquí, hay un error)
            UPModal.alert({ title: 'Error al publicar', body: errorMsg || 'Publicación inválida.' });
            muro.stream.setLoaderStatus(false);
        },

        ajaxPost: (data) => {
            loading.start(); // Loader global (además del visual)
            
            const params = $.param({
                adj: muro.stream.adjunto,
                data: data,
                pid: MURO_CONFIG.PID
            });

            $.post(`/muro-stream.php?do=post&type=${muro.stream.type}`, params)
                .done(req => {
                    const { status, message } = handleServerResponse(req, 'Error al publicar');
                    
                    if (status === 1) {
                        // Inserción limpia y animada
                        if ($('[data-new-shout] .empty').length) {
                             $('#wall-content .empty').remove();
                        }
                        $('[data-new-shout]').prepend($(message).fadeIn('slow'));
                        
                        // Limpieza de UI
                        const plax = $('#wall').attr('placeholder');
                        $('#wall').val('').attr({ placeholder: plax }).focus();
                        muro.stream.load('status', $('#stMain'));
                        $('.input-append').html('');
                    }
                })
                .always(() => {
                    // Limpieza centralizada
                    muro.stream.setLoaderStatus(false);
                    loading.end();
                });
        },

        loadMore: (type) => {
            if (muro.stream.status === 1) return false;
            muro.stream.setLoaderStatus(true);
            
            const $morePubs = $('.more-pubs');
            $morePubs.find('span[role="button"]').hide();
            $morePubs.find('.svg').show();

            loading.start();
            
            $.post(`/muro-stream.php?do=more&type=${type}`, { pid: MURO_CONFIG.PID, start: muro.stream.total })
                .done(req => {
                    const { status, message } = handleServerResponse(req, 'Error al cargar');
                    
                    if (status === 1) {
                        $('#' + type + '-content').append(message);
                        
                        // 💡 FRAGILIDAD: Se asume que el servidor envía el total en #total_pubs
                        const total_pubs = parseInt($('#total_pubs').attr('val') || '0', 10);
                        
                        let msg = (type === 'news' && total_pubs < 0) 
                            ? 'Solo puedes ver las &uacute;ltimas 100 publicaciones.' 
                            : 'No hay m&aacute;s mensajes para mostrar.';
                            
                        if (total_pubs === 0 || total_pubs < muro.stream.show) {
                            $morePubs.html(msg).css('padding','10px');
                        } else {
                            muro.stream.total += total_pubs;
                        }
                        $('#total_pubs').remove(); // Remover el marcador de conteo

                    }
                })
                .always(() => {
                    $morePubs.find('span[role="button"]').show();
                    $morePubs.find('.svg').hide();
                    muro.stream.status = 0;
                    loading.end();
                });
        },
        
        loader: (active, text = 'Publicando') => {
            const $muroStream = $('#muroStrem');
            if (active) {
                // Usar Template Literals
                $muroStream.append(`<div class="shout-status fw-semibold position-absolute z-3">${text}...</div>`);
            } else {
                $muroStream.find('.shout-status').remove();
            }
        }
    },
    
    // ===================================
    // LIKES Y COMENTARIOS
    // ===================================

    // La función like_this ya usa 'json', se refactoriza para usar .always()
    like_this: (id, type, obj) => {
        muro.stream.status = 1;
        loading.start();
        
        $.post(`/muro-likes.php`, `id=${id}&type=${type}`, null, { responseType: 'json' })
            .done(req => {
                const emptyText = !empty(req.text);
                if (req.status === 'ok') {
                    $(obj).text(req.link);
                    
                    const $parent = $(obj).parent().parent();
                    
                    if (type === 'pub') {
                        $(`#like_text--${id}`).html(req.text).parent().parent().toggle(emptyText);
                        if(emptyText) $(`#comment_pub--${id}`).show();
                    } else {
                        $(`#like_comment--${id}`).text(req.text).parent().toggle(emptyText);
                    }
                } else {
                    UPModal.alert({ title: 'Error:', body: req.text });
                }
            })
            .always(() => {
                 muro.stream.status = 0;
                 loading.end();
            });
    },

    show_likes: (id, type) => {
        muro.stream.status = 1;
        loading.start();

        $.post(`/muro-likes.php?do=show`, { id, type }, null, { responseType: 'json' })
            .done(req => {
                const sStatus = parseInt(req.status, 10);
                if (sStatus === 1 && Array.isArray(req.data)) {
                    let sHtml = '<ul id="show_likes">';
                    req.data.forEach(({ user_name, user_id }) => {
                        const src = `${basePath}/files/images/avatar/${user_id}.webp`;
                        sHtml += `<li>
                            <a href="${basePath}/perfil/${user_name}"><img src="${src}" class="avatar avatar-5"></a>
                            <div class="name fw-semibold"><a href="${basePath}/perfil/${user_name}">${user_name}</a></div>
                        </li>`;
                    });
                    sHtml += '</ul>';
                    
                    // Mostramos
                    UPModal.setModal({
                        title: 'Personas a las que les gusta',
                        body: sHtml, // Usar sHtml
                        buttons: { confirmShow: false, cancelShow: true, cancelTxt: 'Cerrar' }
                    });
                } else {
                    UPModal.alert({ title: 'Error', body: req.data || 'Error desconocido' });
                }
            })
            .always(() => {
                muro.stream.status = 0;
                loading.end();
            });
    },

    show_comment_box: (id) => $(`#comment_pub--${id}`).slideDown(),

    comentar: (id) => {
        const idComment = `#comment--${id}`;
        const $commentInput = $(idComment);
        let commentText = $commentInput.val();
        
        if (muro.stream.status === 1) return false;
        
        if (empty(commentText) || commentText === $commentInput.attr('title')) {
            $commentInput.focus(); 
            return false;
        }

        muro.stream.status = 1;
        loading.start();
        
        const param = $.param({ data: commentText, pid: id });

        $.post(`/muro-stream.php?do=repost`, param)
            .done(req => {
                const { status, message } = handleServerResponse(req, 'Error al comentar');
                
                if (status === 1) {
                    $(`#list_comments--${id}`).append(message).fadeIn('slow');
                    $commentInput.val('');
                }
            })
            .always(() => {
                muro.stream.status = 0;
                loading.end();
            });
    },

    more_comments: (id, obj) => {
        if (muro.stream.status === 1) return false;
        muro.stream.status = 1;
        
        const $finder = $(obj).parent();
        $finder.find('span[role="button"]').hide();
        $finder.find('img').show();
        
        loading.start();
        
        $.post(`/muro-stream.php?do=more_comments`, `pid=${id}`)
            .done(req => {
                const { status, message } = handleServerResponse(req, 'Error al cargar comentarios');

                if (status === 1) {
                     $(`#list_comments--${id}`).html(message);
                }
            })
            .always(() => {
                $finder.find('span[role="button"]').show();
                $finder.find('img').hide();
                muro.stream.status = 0;
                loading.end();
            });
    },
    
    load_atta: (type, ID, obj) => {
        const $obj = $(obj);
        switch(type) {
            case 'foto':
                // Usamos Template Literal para la imagen
                $obj.addClass('image-open').html(`<img src="${ID}" class="w-100 h-100 object-fit-cover pe-none" />`);
                break;
            case 'video':
                $obj.addClass('only-video');
                $('.muro-video--description').remove();
                break;
        }
    },
    
    del_pub: (id, type) => {
        const txt_type = (type === 1) ? 'publicaci&oacute;n' : 'comentario';
        const txt_aux = (type === 1) ? 'a' : 'e';
        
        UPModal.setModal({
            title: `Eliminar ${txt_type}`,
            body: `¿Seguro que quieres eliminar est${txt_aux} ${txt_type}?`,
            buttons: {
                confirmTxt: `Eliminar ${txt_type}`,
                // Usamos el nombre completo de la función en la acción
                confirmAction: `muro.eliminar(${id}, ${type})`, 
                cancelShow: true
            }
        });
    },
    
    eliminar: (id, type) => {
        muro.stream.status = 1;
        const snd_type = (type === 1) ? 'pub' : 'cmt';
        
        loading.start();
        
        $.post(`/muro-stream.php?do=delete`, `id=${id}&type=${snd_type}`)
            .done(req => {
                const { status } = handleServerResponse(req, 'Error al eliminar');

                if (status === 1) {
                    UPModal.close();
                    // Usar find() para asegurar que el elemento se oculte y elimine correctamente
                    $(`#${snd_type}_${id}`).fadeOut().remove(); 
                }
            })
            .always(() => {
                muro.stream.status = 0;
                loading.end();
            });
    }
};

// Exportar los módulos para el uso externo (como en los onclicks del HTML)
export { perfil, actividad, muro };


// =========================================================
// INICIALIZACIÓN / READY
// =========================================================

/** Filtro de Pestañas (Se deja aquí para evitar la necesidad de exportar muro/actividad) **/
$('.filter-item').on('click', e => {
    // 💡 OPTIMIZACIÓN: Se asume que el elemento clicado o su ancestro tiene data-type
    const type = parseInt($(e.target).closest('[data-type]').data('type'), 10);
    if (!isNaN(type)) {
        // La importación debe ser manejada externamente si es un módulo diferente,
        // pero aquí la dejamos como está por fidelidad a la estructura original.
        importModule('TabsFilter.js', 'handleLoadFilter', type); 
    }
});


$(() => {
    // 1. Enviar Comentario con Enter (Listener más limpio)
    $('textarea[name="add_wall_comment"]').on("keypress", function(tecla) {
        if (tecla.code === 'Enter' || tecla.charCode === 13) {
            const pub_id = parseInt($(this).closest('form').find('input[name="pid"]').val(), 10);
            if (!isNaN(pub_id)) {
                muro.comentar(pub_id);
            }
            return false;
        }
    });

    // 2. Carga de Pestañas de Perfil
    $('.userPerfil--item').on('click', function() {
        const obj = $(this);
        const classObj = '.userPerfil--item';
        // La importación debe ser manejada externamente
        importModule('TabsFilter.js', 'loadTabs', { obj, classObj }); 
    });

    // 3. Inicialización del Muro (si existe)
    const $wallDiv = $('#wall');
    if ($wallDiv.length > 0) {
        importModule('ContentEditable.js', 'handleContentEditable', $wallDiv);
    }
    
    // 4. Adjuntar archivo con Pegar (Pasting)
    $(window).on('paste', (response) => {
        const items = response.originalEvent?.clipboardData?.items;
        if (!items) return;
        
        for (let i = 0; i < items.length; i++) {
            if (items[i].type.indexOf('image') !== -1) {
                const file = items[i].getAsFile();
                muro.stream.type = 'foto';
                loading.start();
                importModule('ImageUpload.js', 'handleImageUploadFn', file);
                break;
            }
        }		
    });

    // 5. Botón Adjuntar
    $('#attach-file').on('click', () => $('.shout__buttons').toggle());

    // 6. Adjuntar listener para subir fotos
    $('input[name="ifoto"]').on('change', function() {
        muro.stream.type = 'foto';
        loading.start();
        importModule('ImageUpload.js', 'handleImageUploadFn', this.files[0]);
    });
});