
"use strict";
var appNossasLojasVersion = "1.0.1";

var mainMap;
var mainMapInstance;
var lojasFiltradas = [];
var lojaPorRaio = [];
var filtroPorLojas = [];
var loadLojaInicial = [];
var arrayDropdown = []

var clearIntervalConsulta;
var latitudeFiltroGeral = '';
var longitudeFiltroGeral = '';

// latInicial, longInicial = latitude e longitude inicial do mapa para apresentaÃ§Ã£o ao carregar o conteÃºdo
//var latInicial = -23.5397812;
//var longInicial = -46.6373616;
var latInicial = -23.539812;
var longInicial = -46.635178;

// zoomInicial: zoom inicial do mapa
var zoomInicial = 13;

// fitMapToShowAllMarkers: se true, ao carregar mapa, tenta apresentar todos os marcadores dentro do espaÃ§o visual
var fitMapToShowAllMarkers = false;

// indicar se o cliente autorizou acesso a sua localizaÃ§Ã£o
var userAlowSharePosition = true;

// mensagem padrÃ£o para quando nÃ£o encontrar loja
var mainMessageNoStoresAvailable = 'No momento não há nenhuma loja na sua região. Mas veja abaixo as lojas mais próximas.';
// ---------------------

// https://pt.stackoverflow.com/questions/179100/como-fazer-para-que-o-local-storage-expire
function localStorageExpires(){for(var e=[],a=(new Date).getTime(),o=0,r=localStorage.length;o<r;o++){var t=localStorage.getItem(localStorage.key(o));t&&/^\{(.*?)\}$/.test(t)&&(t=JSON.parse(t)).expires&&t.expires<=a&&e.push(localStorage.key(o))}for(o=e.length-1;0<=o;o--)localStorage.removeItem(e[o])}localStorageExpires();
localStorageExpires();//Auto executa a limpeza
function setLocalStorage(e,t,a){var i=(new Date).getTime()+6e4*a;localStorage.setItem(e,JSON.stringify({value:t,expires:i}))}
function getLocalStorage(a){localStorageExpires();var e=localStorage[a];return!(!e||!/^\{(.*?)\}$/.test(e))&&JSON.parse(e).value}

function rnkLog() {
  console.log.apply(
    console,
    [" Stores (v" + appNossasLojasVersion + "): "].concat(
      Array.from(arguments)
    )
  );
};

function debounce(func, wait, immediate) {
	var timeout;
	return function() {
		var context = this, args = arguments;
		var later = function() {
			timeout = null;
			if (!immediate) func.apply(context, args);
		};
		var callNow = immediate && !timeout;
		clearTimeout(timeout);
		timeout = setTimeout(later, wait);
		if (callNow) func.apply(context, args);
	};
};

function addMyLocationMarker(lat, lng) {
  if (!mainMap || !lat || !lng) {
    return;
  }

  var marker = new google.maps.Marker({
    position: { lat: parseFloat(lat), lng: parseFloat(lng) },
    map: mainMap,
    title: 'Você está aqui!'
  });

  marker.setMap(mainMap);
};

function popularLojaPorRaio(lojas) {
  lojaPorRaio = [];
  if (!lojas || !lojas.length) {
    return;
  }
  for (var item of lojas) {
    var valorInicialLatLng = {
      lat: latInicial,
      lng: longInicial,
    };
  
    var valorGeralLatLng = {
      lat: item.latitude,
      lng: item.longitude
    }
  
    var raio = $.calcularDistancia(valorInicialLatLng, valorGeralLatLng);
    if (raio < 200000) {
      lojaPorRaio.push(item);
    } 
  }
};

$.montaDropDown = function () {
  var dropdown = '<div class="rnk-dropdown d-none"><ul></ul></div>'

  $('.rnk-form-buscar-loja .rnk-input-container').append(dropdown);
};
$.getLojasMasterdata = function (range, populateInicialLoad) {

  range = typeof range !== "undefined" ? range : "0-50";

  //config.headers["REST-Range"] = "resources=" + range;

  //var url ="/" + config.store +  "/dataentities/" + config.entity +  "/search?_fields=picliente,nome,longitude,latitude,logradouro,bairro,cidade,estado,cep,horasemana,horariofimsemana,estacionamento,retirada,delivery,horarioferiados,loja_propria,loja_parceira,nome_loja_parceira,tel_1,tel_2";
  lojasFiltradas = [];
  var key = 'stores-vtex-masterdata-range-' + appNossasLojasVersion + '-' + range;
  var promise = new Promise(function (resolve, reject) {
    var data = getLocalStorage(key);
    var fn = function (response) {
      if (response && response.length) {
        for (var loja of response) {
          if (loja.nome) {
            lojasFiltradas.push(loja);
            if (populateInicialLoad) {
              loadLojaInicial.push(loja);
            }
          }
        }
        resolve();
      } else {
        reject();
      }
    };
    if (data) {
      fn(data);
      return;
    }

    $.ajax({
      cache: false,
      url: 'index.php?route=information/maps/data',
      type: "GET",
      dataType: "json",
      crossDomain: true,
     // headers: config.headers,
    })
      .done(function (response) {
        setLocalStorage(key, response, 5);
        fn(response);
      })
      .fail(function (jqXHR, textStatus) {
        if (jqXHR.responseText) {
          if ($.parseJSON(jqXHR.responseText)) {
            var errorM = $.parseJSON(jqXHR.responseText).Message;
            reject();
            return errorM;
          }
        }
      });
  });
  return promise;
};

function mountInitialMapWithStores() {
  var storesNearby = true;
  popularLojaPorRaio(lojasFiltradas);
  if (lojasFiltradas.length) {
    if (!lojaPorRaio.length && userAlowSharePosition) {
      storesNearby = false;
      rnkLog("Não existem lojas na região onde o cliente está");
    }
    mainMapInstance = $.rnkCreateMap({ data: lojasFiltradas, validacao: !storesNearby });
  }
}

(function () {
  function displayLocationInfo(position) {
    latInicial = position.coords.latitude;
    longInicial = position.coords.longitude;
    userAlowSharePosition = true;
    rnkLog(`latitude: ${latInicial} | longitude: ${longInicial}`);
    mountInitialMapWithStores();
  }

  function handleLocationError(error) {
    switch (error.code) {
      case 3:
        rnkLog("[navigator.geolocation] ...timeout");
        // timeout was hit, meaning nothing's in the cache
        // let's provide a default location:
        // displayLocationInfo({
        //   coords: { longitude: latInicial, latitude: longInicial },
        // });

        // now let's make a non-cached request to get the actual position
        navigator.geolocation.getCurrentPosition(
          displayLocationInfo,
          handleLocationError
        );
        break;
      case 2:
        rnkLog("[navigator.geolocation] ...device can't get data");
        break;
      case 1:
        rnkLog("[navigator.geolocation] ...user said no");
        break;
      default:
        rnkLog("[navigator.geolocation] ...", error);
        break;
    }
  }

  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      displayLocationInfo,
      handleLocationError,
      { maximumAge: 1500000, timeout: 0 }
    );
  }
})();

function loadMainData() {
  $.getLojasMasterdata("0-999", true).then(function () {
    mountInitialMapWithStores();

    var filtroLojaInicial = $.organizaLojasParceiras(lojaPorRaio);
    $.montaFiltroPorLoja(filtroLojaInicial);

    // AÃ§Ã£o do filtro por loja
    $.montaFiltroPorLojaAction(latInicial, longInicial);
    //$.montaFiltroPorLojaAction(longInicial,latInicial);
  });
};

(function () {
  $.montaDropDown();

  $("form.rnk-form-buscar-loja").on("submit", function (event) {
    event.preventDefault();
  });

  $("#input-cep").on('input change paste', debounce(function () {
    var $digitado = $(this);
    var texto = $digitado.val().trim();
    lojasFiltradas = [];
    arrayDropdown = [];

    clearTimeout(clearIntervalConsulta);
    if (!!texto && !isNaN(Number(texto.replace("-", "")))) {
      clearIntervalConsulta = setTimeout(function () {
        var urlGoogle = "https://maps.google.com/maps/api/geocode/json?address=" + texto.replace("-", "") +"&key=AIzaSyDi6VQ4SWn8Ek-PbA4bouwZ_KOSe8Xa6TY";
        $.ajax({
          url: urlGoogle,
          dataType: "json",
          method: "GET",
        }).done(function (response) {
          if (response.status == "OK") {
            var valorInicialLatLng = {
              lat: response.results[0].geometry.location.lat,
              lng: response.results[0].geometry.location.lng,
            };

            arrayDropdown = loadLojaInicial
              .map(function (item) {
                var valorGeralLatLng = {
                  lat: item.latitude,
                  lng: item.longitude,
                };

                item.dist = $.calcularDistancia(
                  valorInicialLatLng,
                  valorGeralLatLng
                );

                return item;
              })
              .filter(function (item) {
                return item.dist < 5000;
              })
              .sort(function (a, b) {
                return a.dist - b.dist;
              });

            $.populaDropdown(arrayDropdown, texto);
          }
        });
      }, 800);
    } else {
      if (!texto) {
        setTimeout(function() {
          $.rnkListarLojas(texto, false);
        }, 1000);
        return;
      }

      $.rnkListarLojas(texto, false);
    }
  }, 500))
  // Mascara de CEP em tempo real
  .keyup(function () {
    var digitado = $(this).val();
    if (!isNaN(Number(digitado))) {
      if (digitado.length > 5) {
        var textoFormatado = digitado.replace(/^([0-9]{5})/g, "$1-");
        $(this).val(textoFormatado);
      }
    }
  });

  // --- Carregar mapa  ---
  loadMainData();
})();

$.calcularDistancia = function (position1, position2) {
  var deg2rad = function (deg) {
      return deg * (Math.PI / 180);
    },
    R = 6371,
    dLat = deg2rad(position2.lat - position1.lat),
    dLng = deg2rad(position2.lng - position1.lng),
    a =
      Math.sin(dLat / 2) * Math.sin(dLat / 2) +
      Math.cos(deg2rad(position1.lat)) *
        Math.cos(deg2rad(position1.lat)) *
        Math.sin(dLng / 2) *
        Math.sin(dLng / 2),
      c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

  return ((R * c * 1000).toFixed());
}

$.organizaLojasParceiras = function (array) {
  var newArray = []
  var uniqueObjectLojaParceira = {}
  var uniqueObjectLojaPropria = {}

  for (var i in array) {
    if (array[i].status) {
      uniqueObjectLojaPropria[array[i]['status']] = array[i]
    }

    if (array[i].loja_parceira) {
      uniqueObjectLojaParceira[array[i]['nome_loja_parceira']] = array[i]
    }

  }

  newArray = [];
  for (var j in uniqueObjectLojaPropria) {
    newArray.push(uniqueObjectLojaPropria[j])
  }

  for (var i in uniqueObjectLojaParceira) {
    newArray.push(uniqueObjectLojaParceira[i])
  }

  return newArray;
}


$.populaDropdown = function (lojasFiltradasArray, valorDigitado) {
  if (valorDigitado.length > 0) {
    $('.rnk-form-buscar-loja .rnk-dropdown').removeClass('d-none');
    $('.rnk-form-buscar-loja .rnk-dropdown ul').html('');
    if (lojasFiltradasArray.length !== 0) {
      var htmlDropdownItems = lojasFiltradasArray.map(function (item) {
        return (
          '<li><a href="javascript:void(0)" data-lat="' +
          item.latitude +
          '" data-lng="' +
          item.longitude +
          '">' +
          item.nome +
          "</a></li>"
        );
      });
      $(".rnk-form-buscar-loja .rnk-dropdown ul").html(htmlDropdownItems);
      // Evento de click no autocomplete do dropdown.
      $.clickItemDropdown(lojasFiltradasArray);
      lojasFiltradas = [];
      arrayDropdown = [];
    } else {
      $(".rnk-form-buscar-loja .rnk-dropdown ul").html('<li><a href="javascript:void(0)">Nenhum loja encontrada</a></li>');
    }
  } else {
    $('.rnk-form-buscar-loja .rnk-dropdown').addClass('d-none');
  }
}

$.rnkListarLojas = function (valorInput, initialLoad) {
  if (!valorInput) {
    $('.rnk-form-buscar-loja .rnk-dropdown').addClass('d-none');
    lojasFiltradas = loadLojaInicial;
    mountInitialMapWithStores();
    return;
  }

  var filtroCepInicio = '';
  var filtroCepFim = '';


  lojasFiltradas = [];
  var valueToCompare = valorInput.toLowerCase();
  
  loadLojaInicial.map(function (loja) {
    if (loja.nome) {
      var cep;
      if (loja.nome.toLowerCase().indexOf(valueToCompare) > -1) {
        cep = loja.cep.replace('-', '');

        filtroCepInicio = loja.cep.substring(0, 2) + '000000'
        filtroCepFim = loja.cep.substring(0, 2) + '999999'

        if (cep >= filtroCepInicio && cep <= filtroCepFim) {
          lojasFiltradas.push(loja)
        }
      }

      else if (loja.bairro.toLowerCase().indexOf(valueToCompare) > -1) {
        cep = loja.cep.replace('-', '');

        filtroCepInicio = loja.cep.substring(0, 2) + '000000'
        filtroCepFim = loja.cep.substring(0, 2) + '999999'

        if (cep >= filtroCepInicio && cep <= filtroCepFim) {
          lojasFiltradas.push(loja)
        }
      }

      else if (loja.estado.toLowerCase().indexOf(valueToCompare) > -1) {
        cep = loja.cep.replace('-', '');

        filtroCepInicio = loja.cep.substring(0, 2) + '000000'
        filtroCepFim = loja.cep.substring(0, 2) + '999999'

        if (cep >= filtroCepInicio && cep <= filtroCepFim) {
          lojasFiltradas.push(loja)
        }
      }

      else if (loja.cidade.toLowerCase().indexOf(valueToCompare) > -1) {
        cep = loja.cep.replace('-', '');

        filtroCepInicio = loja.cep.substring(0, 2) + '000000'
        filtroCepFim = loja.cep.substring(0, 2) + '999999'

        if (cep >= filtroCepInicio && cep <= filtroCepFim) {
          lojasFiltradas.push(loja)
        }
      }

      else if (loja.logradouro.toLowerCase().indexOf(valueToCompare) > -1) {
        cep = loja.cep.replace('-', '');

        filtroCepInicio = loja.cep.substring(0, 2) + '000000'
        filtroCepFim = loja.cep.substring(0, 2) + '999999'

        if (cep >= filtroCepInicio && cep <= filtroCepFim) {
          lojasFiltradas.push(loja)
        }
      } else {
        cep = loja.cep.replace('-', '');

        filtroCepInicio = valorInput.substring(0, 2) + '000000'
        filtroCepFim = valorInput.substring(0, 2) + '999999'

        if (cep >= filtroCepInicio && cep <= filtroCepFim) {
          lojasFiltradas.push(loja)
        }
      }
    }
  });

  if (!initialLoad) {
    // Remover display none do dropdown
    $('.rnk-form-buscar-loja .rnk-dropdown').removeClass('d-none');

    // Popular dropdown (autocomplete)
    $.populaDropdown(lojasFiltradas, valorInput);

    // Verifica se o input esta vazio, caso true, zera o array de lojas filtradas e remove o valor do dropdown
    $.validaInput(valorInput);

    // Limpa lojas carregadas
    $('.rnk-component-lista-loja .row').html("");
  }
};

$.validaInput = function (valorInput) {
  // Verifica se o input esta vazio, caso true, zera o array de lojas filtradas e remove o valor do dropdown
  if (valorInput == null || valorInput == undefined || valorInput == '') {
    lojasFiltradas = []

    setTimeout(function () {
      $('.rnk-dropdown').addClass('d-none');
      $('.rnk-dropdown ul').html('');
      $('.rnk-input-container').find('#input-cep').val('');
    }, 300)
  }
}

$.clickItemDropdown = function (arrayLoja) {
  var latitudeAlvo = '';
  var longitudeAlvo = '';
  var valorInicialLatLng;
  var valorGeralLatLng;

  // Evento de click no autocompelte do dropdown.
  $('#frmFiltro .rnk-input-container .rnk-dropdown ul li a').on('click', function (e) {
    e.preventDefault();
    latitudeAlvo = $(this).attr('data-lat');
    longitudeAlvo = $(this).attr('data-lng');

    // FuncÃ§Ã£o que cria o mapa
    if (arrayLoja.length == 0) {
      mainMapInstance = $.rnkCreateMap({
        data: lojaPorRaio,
        validacao: false,
        fitMap: false,
      });

    } else {
      lojaPorRaio = [];

      $(arrayLoja).each(function (i, item) {
        valorInicialLatLng = {
          lat: latitudeAlvo,
          lng: longitudeAlvo
        }

        valorGeralLatLng = {
          lat: item.latitude,
          lng: item.longitude
        }

        var raio = $.calcularDistancia(valorInicialLatLng, valorGeralLatLng);
        if (raio < 5000) {
          lojaPorRaio.push(item);
        }
      });

      mainMapInstance = $.rnkCreateMap({
        data: lojaPorRaio,
        validacao: false,
        latAlvo: latitudeAlvo,
        lngAlvo: longitudeAlvo,
        fitMap: true,
      });

      // Montando barra de filtro por loja
      $('.rnk-filtro-loja').html('');
      var newArrayFiltroLoja = $.organizaLojasParceiras(lojaPorRaio)
      $.montaFiltroPorLoja(newArrayFiltroLoja);

      // AÃ§Ã£o do filtro por loja
      $.montaFiltroPorLojaAction(latitudeAlvo, longitudeAlvo);
    }

    $('.rnk-dropdown').addClass('d-none');
    $('.rnk-dropdown ul').html('');

    // Adiciona loja clicada como value do input
    $('.rnk-input-container').find('#input-cep').val($(this).text());
  })
}

$.montaFiltroPorLoja = function (lojaPorRaio) {
  var htmlContent = '';
  if (lojaPorRaio.length) {
    htmlContent += '<div class="rnk-filtro-header">';
    htmlContent += '   <p>Filtre por loja</p>';
    htmlContent += '</div>';
    htmlContent += '<div class="rnk-filtro-body">';
    htmlContent += '   <ul>';

    $(lojaPorRaio).each(function (i, item) {
      htmlContent += '   <li>';
      /*if (item.loja_parceira) {
        htmlContent += '      <a href="javascript:void(0)" class="d-block ' + item.nome_loja_parceira + '" data-filtro="' + item.nome_loja_parceira + '"></a>';
      } else {
        htmlContent += '      <a href="javascript:void(0)" class="d-block rnk-swift" data-filtro="rnk-swift"></a>';
      }
      htmlContent += '   </li>';*/
         if (item.status) {
        htmlContent += '      <a href="javascript:void(0)" class="d-block ' + item.nome + '" data-filtro="' + item.nome + '"></a>';
      } else {
        htmlContent += '      <a href="javascript:void(0)" class="d-block rnk-swift" data-filtro="rnk-swift"></a>';
      }
      htmlContent += '   </li>';
    })

    htmlContent += '   </ul>';
    htmlContent += '</div>';
  }

  $('.rnk-filtro-loja').html(htmlContent);
}

$.montaFiltroPorLojaAction = function (latitudeAlvo, longitudeAlvo) {
  $('.rnk-filtro-loja ul li > a').on('click', function (e) {
    e.preventDefault();
    var clicado = $(this).attr('data-filtro');
    //Limpando row de cards
    $(".rnk-component-lista-loja .row").html('');

    // Removendo elemento antes de adicionar
    filtroPorLojas = []
    $('.rnk-filtro-loja ul li').removeClass('active');
    $('.rnk-filtro-loja ul li > button').remove();

    $(lojaPorRaio).each(function (i, item) {
      if (item.status && item.nome === clicado) {
        //Adicionando objeto com os valores no array que vai ser passado pra funÃ§Ã£o de mapa
        filtroPorLojas.push(item);
      }

      if (item.status && clicado === 'rnk-swift') {
        // Adicionando lojas proprias no array caso a condiÃ§Ã£o seja verdadeira
        filtroPorLojas.push(item)
      }

    })

    // Adicionando classe ativo e botao de fechar
    $(this).parent().addClass('active');
    $(this).parent().prepend('<button class="close-filter"><i class="fas fa-times"></i></button>');

    // Chamando funÃ§Ã£o de mapa pra adicionar apenas o elemento filtrado.
    mainMapInstance = $.rnkCreateMap({
      data: filtroPorLojas,
      validacao: false,
      fitMap: true,
    });

    // AÃ§Ã£o de fechar filtro por loja
    $.closeFiltroPorLoja(latitudeAlvo, longitudeAlvo);
  })
}

$.closeFiltroPorLoja = function (latitudeAlvo, longitudeAlvo) {
  $('.rnk-filtro-loja ul li > button').click(function () {
    //Limpando row de cards
    $(".rnk-component-lista-loja .row").html('');

    $(this).parent().removeClass('active');
    $(this).remove();

    mainMapInstance = $.rnkCreateMap({
      data: lojaPorRaio,
      validacao: false,
      latAlvo: latitudeAlvo,
      lngAlvo: longitudeAlvo,
    });
  })
}

$.rnkCreateMap = function (props) {
  var data = props.data;
  var validacao = props.validacao;
  var latAlvo = props.latAlvo;
  var lngAlvo = props.lngAlvo;
  var fitMap = props.fitMap || fitMapToShowAllMarkers;

  var $msg = $(".rnk-component-lista-loja-msg .row");
  var stores;

  var positionMarker = [];
  var markers = [];
  var map, marker, results, store;

  $msg.html("");

  var ourStores = {
    sortStoresByLatLng: function(lat, lng) {
      stores = stores.map(function(item) {
        if (item.latitude && item.longitude) {
          var valorInicialLatLng = {
            lat: lat || latInicial,
            lng: lng || longInicial
          };

          var valorGeralLatLng = {
            lat: item.latitude,
            lng: item.longitude
          };

          item.dist = $.calcularDistancia(valorInicialLatLng, valorGeralLatLng);
        }

        return item;
      }).sort(function(a, b) {
        return a.dist - b.dist;
      });
    },
    init: function (data) {
      stores = (data || []);
      this.sortStoresByLatLng();
      if (validacao) {
        $msg.html(
          '<p class="text-center w-100 mt-4" style="color: #f00;">' + mainMessageNoStoresAvailable + '</p>'
        );
      }
      this.newMap();
      setTimeout(
        function () {
          this.addMakers();
          this.addCards();
        }.bind(this),
        100
      );
    },

    newMap: function () {
      var removePinAjudaMapa = [
        {
          featureType: "administrative",
          elementType: "geometry",
          stylers: [
            {
              visibility: "off",
            },
          ],
        },
        {
          featureType: "poi",
          stylers: [
            {
              visibility: "off",
            },
          ],
        },
        {
          featureType: "road",
          elementType: "labels.icon",
          stylers: [
            {
              visibility: "off",
            },
          ],
        },
        {
          featureType: "transit",
          stylers: [
            {
              visibility: "off",
            },
          ],
        },
      ];

      map = new google.maps.Map(document.getElementById("maps"), {
        center: new google.maps.LatLng(latInicial, longInicial),
        zoom: zoomInicial,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        styles: removePinAjudaMapa,
      });
     // console.log(map);

      // alterar a orde da lista dos cards conforme atualizaÃ§Ã£o do centro do mapa
      map.addListener('center_changed', debounce(function() {
        var coords = map.getCenter();
        this.sortStoresByLatLng(coords.lat(), coords.lng());
        this.addCards();
      }.bind(this), 1000));

      mainMap = map;
    },

    scrollTop: function () {
      $("html,body").animate(
        {
          scrollTop: $("#maps").offset().top - 115,
        },
        800,
        function () {}
      );
    },

    createHtmlCard: function (store, i) {
      var htmlCard =
        '<div id="store-card-' +
        (i + 1) +
        '" class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-3">';
      htmlCard += '<div class="card">';

      if (store.status === 1) {
        htmlCard +=
          ' <div class="card-header ' + ourStores.slugify(store.nome) + '">';
        htmlCard += " </div>";
      }

      /*if (store.loja_propria) {
        htmlCard += ' <div class="card-header rnk-swift">';
        htmlCard += " </div>";
      }*/

      htmlCard += ' <div class="card-body">';
      // 1 {
      htmlCard += '   <div class="rnk-title-container">';
      if (store.status === 1) {
        htmlCard +=
          '      <h2 class="rnk-title-card rnk-title-loja-parceira"><a href="' + store.site + '">' + store.nome + '</a></h2>';
      }

     /* if (store.loja_propria) {
        htmlCard +=
          '      <h2 class="rnk-title-card rnk-title-loja-propria">Loja Swift</h2>';
      }*/
      htmlCard += "   </div>";
      // } 1
      htmlCard += '   <h3 class="d-none">EndereÃ§o</h3>';

      if (store.cidade || store.bairro) {
        htmlCard += '   <div class="rnk-endereco">';
        htmlCard += '     <i class="fa fa-map-marker-alt"></i>';
        htmlCard += "     <div>";
        htmlCard +=
          '       <span class="bairro">' +
          store.cidade +
          " - " +
          store.bairro +
          "</span>";
        htmlCard +=
          '       <span class="endereco">' + store.logradouro + "</span>";
        htmlCard += "     </div>";
        htmlCard += "   </div>";
      }

      htmlCard += '   <h3 class="d-none">HorÃ¡rio de Funcionamento</h3>';

      if (store.horasemana || store.horariofimsemana || store.horarioferiados) {
        htmlCard += '   <div class="rnk-dias-e-horarios">';
        htmlCard += '     <i class="fa fa-clock"></i>';
        htmlCard += "     <ul>";
        if (store.horasemana) {
          htmlCard +=
            "       <li>Segunda a SÃ¡bado: <span>" +
            store.horasemana +
            "</span></li>";
        }
        if (store.horariofimsemana) {
          htmlCard +=
            "       <li>Domingo e Feriado: <span>" +
            store.horariofimsemana +
            "</span></li>";
        }
        if (store.horarioferiados) {
          htmlCard += "       <li>Feriados: " + store.horarioferiados + "</li>";
        }
        htmlCard += "     </ul>";
        htmlCard += "   </div>";
      }

      htmlCard += '   <h3 class="d-none">ServiÃ§os DisponÃ­veis</h3>';

      if (store.retirada || store.delivery) {
        htmlCard += '   <div class="rnk-servicos-disponiveis">';
        htmlCard += '     <i class="fal fa-badge-check"></i>';
        htmlCard += "     <ul>";
        if (store.retirada) {
          htmlCard += "       <li>Retirada em Loja</li>";
        }
        if (store.delivery) {
          htmlCard += "       <li>Delivery</li>";
        }
        htmlCard += "     </ul>";
        htmlCard += "   </div>";
      }

      htmlCard += " </div>";

      htmlCard += "</div>";
      htmlCard += "</div>";

      if (store.nome != null) {
        var $card = $(htmlCard);

        $card.on('click',  function(event) {
          if (!mainMap || !+store.longitude || !+store.latitude) {
            return;
          }
          event.preventDefault();
          mainMap.setZoom(zoomInicial + 2);
          mainMap.setCenter({ lat: +store.latitude, lng:  +store.longitude });
          //console.log(store.longitude);
        });

        return $card;
      }
      return false;
    },

    addCards: function () {
      var i = 0;
      var htmlCards = [];
      for (var storeRaw of stores) {
        var htmlCard = ourStores.createHtmlCard(storeRaw, i);
        if (htmlCard) {
          htmlCards.push(htmlCard);
        }

        i = i + 1;
      }
      results.html(htmlCards);
    },
    addMakers: function () {
      var currentLocation;
      var latlngbounds = new google.maps.LatLngBounds();
      var iconPin;
      var i = 0;

      results = $(".rnk-component-lista-loja .row");
      
      for (var storeRaw of stores) {
        store = new google.maps.LatLng(storeRaw.latitude, storeRaw.longitude);

        if (storeRaw.status) {
          iconPin =
            "https://swiftbr.vteximg.com.br/arquivos/rnk-swift-pin-loja-propria.png";
        } else {
          iconPin =
            "https://swiftbr.vteximg.com.br/arquivos/rnk-pin-carrinho-swift.png";
        }

        marker = new google.maps.Marker({
          position: store,
          map: map,
          icon: iconPin,
        });

        marker.setMap(map);
        markers.push(marker);
        if (fitMap) {
          latlngbounds.extend(marker.position);
        }

        if (i == 0 && latAlvo && lngAlvo && fitMap) {
          currentLocation = {
            lat: Number(latAlvo),
            lng: Number(lngAlvo),
          };
          map.setCenter(currentLocation);
        }

        var innerContentElement = "";
        innerContentElement += '<div class="rnk-pin-mapa">';
        if (storeRaw.status === true) {
          innerContentElement +=
            '<div class="rnk-pin-header ' + storeRaw.nome + '">';
          innerContentElement += "<h3> <a href=" + store.site + ">" + store.nome + "</a> </h3>";
        }
        if (storeRaw.loja === true) {
          innerContentElement +=
            '<div class="rnk-pin-header rnk-swift-loja-propria">';
          innerContentElement += "<h3>Loja Swift</h3>";
        }
        innerContentElement += "</div>";
        innerContentElement += '<div class="rnk-pin-body">';
        innerContentElement += '<h3 class="d-none">EndereÃ§o</h3>';

        if (storeRaw.cidade || storeRaw.bairro) {
          innerContentElement += '<div class="rnk-endereco">';
          innerContentElement += '<i class="fa fa-map-marker-alt"></i>';
          innerContentElement += "<div>";
          innerContentElement +=
            '<span class="bairro">' +
            storeRaw.cidade +
            " - " +
            storeRaw.bairro +
            "</span>";
          innerContentElement +=
            '<span class="endereco">' + storeRaw.logradouro + "</span>";
          innerContentElement += "</div>";
          innerContentElement += "</div>";
        }

        innerContentElement +=
          '<h3 class="d-none">HorÃ¡rio de Funcionamento</h3>';

        if (
          storeRaw.horasemana ||
          storeRaw.horafimsemana ||
          storeRaw.horarioferiados
        ) {
          innerContentElement += '<div class="rnk-dias-e-horarios">';
          innerContentElement += '<i class="fa fa-clock"></i>';
          innerContentElement += "<ul>";
          innerContentElement +=
            "<li>Segunda a S\u00e1bado: <span>" +
            storeRaw.horasemana +
            "</span></li>";
          innerContentElement +=
            "<li>Domingo e Feriado: <span>" +
            storeRaw.horariofimsemana +
            "</span></li>";
          innerContentElement +=
            "<li>Feriados: " + storeRaw.horarioferiados + "</li>";
          innerContentElement += "</ul>";
          innerContentElement += "</div>";
        }

        innerContentElement += '<h3 class="d-none">ServiÃ§os DisponÃ­veis</h3>';

        if (storeRaw.retirada || storeRaw.delivery) {
          innerContentElement += '<div class="rnk-servicos-disponiveis">';
          innerContentElement += '<i class="fal fa-badge-check"></i>';
          innerContentElement += "<ul>";
          if (storeRaw.retirada) {
            innerContentElement += "<li>Retirada em Loja</li>";
          }
          if (storeRaw.delivery) {
            innerContentElement += "<li>Delivery</li>";
          }

          innerContentElement += "</ul>";
          innerContentElement += "</div>";
        }

        innerContentElement += "</div>";
        innerContentElement += "</div>";

        var ib = new InfoBox({
          closeBoxMargin: "15px 10px 0px 0px",
          closeBoxURL: "http://swiftbr.vteximg.com.br/arquivos/close-email.png",
        });

        marker.addListener(
          "click",
          (function (c) {
            return function () {
              ib.setContent(c);
              ib.open(map, this);
            };
          })(innerContentElement)
        );

        if (fitMap) {
          map.fitBounds(latlngbounds);
        }

        i = i + 1;
      }

      if (userAlowSharePosition) {
        addMyLocationMarker(latInicial, longInicial);
      }
    },

    clearMarkersSearch: function () {
      for (var i = 0; i < positionMarker.length; i++) {
        positionMarker[i].setMap(null);
      }
      positionMarker = [];
    },

    handleLocationError: function (browserHasGeolocation, infoWindow, pos) {
      infoWindow.setPosition(pos);
      infoWindow.setContent(
        browserHasGeolocation
          ? "Erro: O serviÃ§o de geolocalizaÃ§Ã£o falhou."
          : "Erro: Seu navegador nÃ£o suporta a localizaÃ§Ã£o geogrÃ¡fica."
      );
    },
   slugify :function(str) {
  
  // Converte o texto para caixa baixa:
  str = str.toLowerCase();
  
  // Remove qualquer caractere em branco do final do texto:
  str = str.replace(/^\s+|\s+$/g, '');

  // Lista de caracteres especiais que serão substituídos:
  const from = "ãàáäâẽèéëêìíïîõòóöôùúüûñç·/_,:;";
  
  // Lista de caracteres que serão adicionados em relação aos anteriores:
  const to   = "aaaaaeeeeeiiiiooooouuuunc------";
  
  // Substitui todos os caracteres especiais:
  for (let i = 0, l = from.length; i < l; i++) {
    str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
  }

  // Remove qualquer caractere inválido que possa ter sobrado no texto:
  str = str.replace(/[^a-z0-9 -]/g, '');
  
  // Substitui os espaços em branco por hífen:
  str = str.replace(/\s+/g, '-');

  return str;
},
  };

  // Chamada do mapa
  ourStores.init(data);

  return this;
};