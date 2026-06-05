(function () {
  'use strict';

  function easeOutCubic(t) {
    return 1 - Math.pow(1 - t, 3);
  }

  function animateStat(el) {
    var target = parseFloat(el.getAttribute('data-display'));
    var valEl  = el.querySelector('.count-val');
    if (!valEl || isNaN(target)) return;

    var isDecimal = target % 1 !== 0;
    var duration  = 1800;
    var start     = null;

    function format(n) {
      return isDecimal ? n.toFixed(1) : Math.round(n).toLocaleString('en-IN');
    }

    function step(timestamp) {
      if (!start) start = timestamp;
      var elapsed  = timestamp - start;
      var progress = Math.min(elapsed / duration, 1);
      var current  = easeOutCubic(progress) * target;
      valEl.textContent = format(current);
      if (progress < 1) {
        requestAnimationFrame(step);
      } else {
        valEl.textContent = format(target);
      }
    }

    requestAnimationFrame(step);
  }

  var stats = document.querySelectorAll('.stat__num[data-display]');
  if (!stats.length) return;

  var observed = false;
  var observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting && !observed) {
        observed = true;
        stats.forEach(animateStat);
        observer.disconnect();
      }
    });
  }, { threshold: 0.25 });

  var impactSection = document.getElementById('impact');
  if (impactSection) observer.observe(impactSection);
})();

/* Mobile nav */
(function () {
  var btn  = document.getElementById('navHamburger');
  var nav  = document.getElementById('siteNav');
  var menu = document.getElementById('navMobile');
  if (!btn || !nav) return;

  btn.addEventListener('click', function () {
    var open = nav.classList.toggle('nav--open');
    btn.setAttribute('aria-expanded', open);
    if (menu) menu.setAttribute('aria-hidden', !open);
  });

  if (menu) {
    menu.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        nav.classList.remove('nav--open');
        btn.setAttribute('aria-expanded', 'false');
        menu.setAttribute('aria-hidden', 'true');
      });
    });
  }
})();

/* Carousel */
(function () {
  var track   = document.getElementById('carouselTrack');
  var prevBtn = document.getElementById('carouselPrev');
  var nextBtn = document.getElementById('carouselNext');
  if (!track) return;

  var cards   = Array.from(track.children);
  var total   = cards.length;
  var current = 0;

  cards.forEach(function (c) { track.appendChild(c.cloneNode(true)); });

  function cardWidth() {
    var card = track.querySelector('.carousel__card');
    var gap  = parseFloat(getComputedStyle(track).columnGap) || 0;
    return card.offsetWidth + gap;
  }

  function goTo(index, animate) {
    track.style.transition = animate === false ? 'none' : 'transform 0.55s cubic-bezier(0.4,0,0.2,1)';
    track.style.transform  = 'translateX(-' + (index * cardWidth()) + 'px)';
  }

  function next() {
    current++;
    goTo(current);
    if (current >= total) {
      setTimeout(function () { current = 0; goTo(0, false); }, 580);
    }
  }

  function prev() {
    if (current <= 0) {
      current = total;
      goTo(total, false);
      setTimeout(function () { current--; goTo(current); }, 20);
    } else {
      current--;
      goTo(current);
    }
  }

  if (nextBtn) nextBtn.addEventListener('click', function () { next(); });
  if (prevBtn) prevBtn.addEventListener('click', function () { prev(); });

  setInterval(next, 3000);
})();

/* Donation Modal */
(function () {
  var overlay    = document.getElementById('donateOverlay');
  var closeBtn   = document.getElementById('donateClose');
  var dots       = Array.from(document.querySelectorAll('.dp-dot'));
  var lines      = Array.from(document.querySelectorAll('.dp-line'));

  var step1      = document.getElementById('donateStep1');
  var step2      = document.getElementById('donateStep2');
  var step3      = document.getElementById('donateStep3');
  var step4      = document.getElementById('donateStep4');

  var amtBtns    = Array.from(document.querySelectorAll('.amt-btn'));
  var customInp  = document.getElementById('customAmount');
  var amtPill    = document.getElementById('amountPill');
  var successMsg = document.getElementById('successMsg');
  var txnEl      = document.getElementById('successTxn');

  var selectedAmount = 500;

  /* open / close */
  function openModal() {
    overlay.removeAttribute('hidden');
    document.body.style.overflow = 'hidden';
    goToStep(1);
  }
  function closeModal() {
    overlay.setAttribute('hidden', '');
    document.body.style.overflow = '';
  }

  document.querySelectorAll('.js-donate').forEach(function (btn) {
    btn.addEventListener('click', openModal);
  });
  closeBtn.addEventListener('click', closeModal);
  overlay.addEventListener('click', function (e) {
    if (e.target === overlay) closeModal();
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !overlay.hasAttribute('hidden')) closeModal();
  });

  /* step progress indicator */
  function goToStep(n) {
    [step1, step2, step3, step4].forEach(function (p, i) {
      if (i + 1 === n) {
        p.classList.remove('donate-panel--hidden');
      } else {
        p.classList.add('donate-panel--hidden');
      }
    });
    dots.forEach(function (d, i) {
      d.classList.remove('dp-active', 'dp-done');
      if (i + 1 < n)  d.classList.add('dp-done');
      if (i + 1 === n) d.classList.add('dp-active');
    });
    lines.forEach(function (l, i) {
      l.classList.toggle('dp-done', i + 1 < n);
    });
    document.querySelector('.donate-modal').scrollTop = 0;
  }

  /* amount selection */
  amtBtns.forEach(function (btn) {
    btn.addEventListener('click', function () {
      amtBtns.forEach(function (b) { b.classList.remove('amt-btn--active'); });
      btn.classList.add('amt-btn--active');
      selectedAmount = parseInt(btn.getAttribute('data-amount'), 10);
      customInp.value = selectedAmount;
    });
  });
  customInp.addEventListener('input', function () {
    amtBtns.forEach(function (b) { b.classList.remove('amt-btn--active'); });
    var v = parseInt(customInp.value, 10);
    if (!isNaN(v) && v > 0) selectedAmount = v;
  });

  function getAmount() {
    var cv = parseInt(customInp.value, 10);
    if (!isNaN(cv) && cv > 0) return cv;
    return selectedAmount;
  }

  function fmtAmount(n) {
    return 'â‚¹' + n.toLocaleString('en-IN');
  }

  /* UPI */
  var UPI_ID = '9711163637@ptyes';

  function buildUpiUri(amount) {
    return 'upi://pay?pa=' + encodeURIComponent(UPI_ID) +
      '&pn=' + encodeURIComponent('Umeed') +
      '&am=' + amount +
      '&cu=INR' +
      '&tn=' + encodeURIComponent('Donation to Umeed');
  }

  function renderQr(amount) {
    var container = document.getElementById('upiQrCode');
    container.innerHTML = '';
    if (typeof QRCode !== 'undefined') {
      new QRCode(container, {
        text:         buildUpiUri(amount),
        width:        180,
        height:       180,
        colorDark:    '#1a1a1a',
        colorLight:   '#ffffff',
        correctLevel: QRCode.CorrectLevel.M
      });
    }
  }

  document.getElementById('upiCopyBtn').addEventListener('click', function () {
    var btn = this;
    navigator.clipboard.writeText(UPI_ID).then(function () {
      btn.textContent = 'Copied!';
      setTimeout(function () { btn.textContent = 'Copy'; }, 2000);
    });
  });

  /* Step 1 */
  document.getElementById('step1Next').addEventListener('click', function () {
    if (getAmount() < 1) { customInp.focus(); return; }
    amtPill.textContent = 'Donating ' + fmtAmount(getAmount());
    goToStep(2);
  });

  /* Step 2 */
  document.getElementById('step2Back').addEventListener('click', function () { goToStep(1); });

  document.getElementById('step2Next').addEventListener('click', function () {
    var name  = document.getElementById('donorName').value.trim();
    var email = document.getElementById('donorEmail').value.trim();
    var phone = document.getElementById('donorPhone').value.trim();
    if (!name)  { document.getElementById('donorName').focus(); return; }
    if (!email) { document.getElementById('donorEmail').focus(); return; }
    if (!phone) { document.getElementById('donorPhone').focus(); return; }
    goToStep(3);
    renderQr(getAmount());
  });

  /* Step 3 */
  document.getElementById('step3Back').addEventListener('click', function () { goToStep(2); });

  document.getElementById('step3Next').addEventListener('click', function () {
    var amount = getAmount();
    var donor  = document.getElementById('donorName').value.trim();
    var email  = document.getElementById('donorEmail').value.trim();
    var phone  = document.getElementById('donorPhone').value.trim();
    var pan    = document.getElementById('donorPAN').value.trim();
    var msg    = document.getElementById('donorMessage').value.trim();
    var anon   = document.getElementById('anonymousCheck').checked;
    var dtype  = document.querySelector('input[name="donationType"]:checked').value;
    var txnId  = 'UMD-' + Math.random().toString(36).substr(2, 6).toUpperCase();
    var suffix = (anon || !donor) ? '!' : ', ' + donor.split(' ')[0] + '!';

    /* Save donation to backend (fire-and-forget) */
    fetch('https://script.google.com/macros/s/AKfycbwl6knEUIXGyKNmTbMXWktHM8BCajCsD2Auqb4bxSCdT9vCpGRykFAC9F3HMl6CfvH8uw/exec', {
      method: 'POST',
      mode: 'no-cors',
      body: JSON.stringify({ type: 'donation', name: donor, email: email, phone: phone, pan: pan, message: msg,
        amount: amount, donation_type: dtype, anonymous: anon }),
    }).catch(function () {});

    successMsg.innerHTML = 'Your contribution of <strong>' + fmtAmount(amount) + '</strong> can support meaningful change.';
    txnEl.textContent    = 'Transaction ID: ' + txnId;
    document.querySelector('.success-title').textContent = 'Thank You' + suffix;
    goToStep(4);
  });

  /* Step 4 */
  document.getElementById('returnHome').addEventListener('click', closeModal);

  document.getElementById('downloadReceipt').addEventListener('click', function () {
    alert('Receipt feature coming soon.');
  });

  document.getElementById('shareBtn').addEventListener('click', function () {
    if (navigator.share) {
      navigator.share({ title: 'I donated to Umeed!', url: window.location.href });
    } else {
      navigator.clipboard && navigator.clipboard.writeText(window.location.href);
      alert('Link copied! Share it with your friends.');
    }
  });
})();

/* Volunteer modal */
(function () {
  var overlay = document.getElementById('volunteerOverlay');
  if (!overlay) return;

  function open()  { overlay.removeAttribute('hidden'); document.body.style.overflow = 'hidden'; }
  function close() { overlay.setAttribute('hidden', ''); document.body.style.overflow = ''; }

  document.querySelectorAll('.js-volunteer').forEach(function (btn) { btn.addEventListener('click', open); });
  document.getElementById('volunteerClose').addEventListener('click', close);
  document.getElementById('volReturnHome').addEventListener('click', close);
  overlay.addEventListener('click', function (e) { if (e.target === overlay) close(); });

  document.getElementById('volSubmit').addEventListener('click', function () {
    var btn   = this;
    var name  = document.getElementById('volName').value.trim();
    var email = document.getElementById('volEmail').value.trim();
    var err   = document.getElementById('volError');
    err.setAttribute('hidden', '');

    if (!name)  { document.getElementById('volName').focus(); return; }
    if (!email) { document.getElementById('volEmail').focus(); return; }

    btn.disabled = true;
    btn.textContent = 'Submitting...';

    fetch('https://script.google.com/macros/s/AKfycbwl6knEUIXGyKNmTbMXWktHM8BCajCsD2Auqb4bxSCdT9vCpGRykFAC9F3HMl6CfvH8uw/exec', {
      method: 'POST',
      mode: 'no-cors',
      body: JSON.stringify({
        type:         'volunteer',
        name:         name,
        email:        email,
        phone:        document.getElementById('volPhone').value.trim(),
        city:         document.getElementById('volCity').value.trim(),
        availability: document.querySelector('input[name="volAvail"]:checked').value,
        skills:       document.getElementById('volSkills').value.trim(),
        message:      document.getElementById('volMessage').value.trim(),
      }),
    })
    .then(function () {
      document.getElementById('volunteerPanel').classList.add('donate-panel--hidden');
      document.getElementById('volunteerSuccess').classList.remove('donate-panel--hidden');
    })
    .catch(function () {
      err.textContent = 'Network error. Please try again.';
      err.removeAttribute('hidden');
      btn.disabled = false;
      btn.textContent = 'Submit Application';
    });
  });
})();

/* Internship modal */
(function () {
  var overlay = document.getElementById('internshipOverlay');
  if (!overlay) return;

  function open()  { overlay.removeAttribute('hidden'); document.body.style.overflow = 'hidden'; }
  function close() { overlay.setAttribute('hidden', ''); document.body.style.overflow = ''; }

  document.querySelectorAll('.js-internship').forEach(function (btn) { btn.addEventListener('click', open); });
  document.getElementById('internshipClose').addEventListener('click', close);
  document.getElementById('intReturnHome').addEventListener('click', close);
  overlay.addEventListener('click', function (e) { if (e.target === overlay) close(); });

  document.getElementById('intSubmit').addEventListener('click', function () {
    var btn   = this;
    var name  = document.getElementById('intName').value.trim();
    var email = document.getElementById('intEmail').value.trim();
    var err   = document.getElementById('intError');
    err.setAttribute('hidden', '');

    if (!name)  { document.getElementById('intName').focus(); return; }
    if (!email) { document.getElementById('intEmail').focus(); return; }

    btn.disabled = true;
    btn.textContent = 'Submitting...';

    fetch('https://script.google.com/macros/s/AKfycbwl6knEUIXGyKNmTbMXWktHM8BCajCsD2Auqb4bxSCdT9vCpGRykFAC9F3HMl6CfvH8uw/exec', {
      method: 'POST',
      mode: 'no-cors',
      body: JSON.stringify({
        type:    'internship',
        name:    name,
        email:   email,
        phone:   document.getElementById('intPhone').value.trim(),
        college: document.getElementById('intCollege').value.trim(),
      }),
    })
    .then(function () {
      document.getElementById('internshipPanel').classList.add('donate-panel--hidden');
      document.getElementById('internshipSuccess').classList.remove('donate-panel--hidden');
    })
    .catch(function () {
      err.textContent = 'Network error. Please try again.';
      err.removeAttribute('hidden');
      btn.disabled = false;
      btn.textContent = 'Submit Application';
    });
  });
})();
