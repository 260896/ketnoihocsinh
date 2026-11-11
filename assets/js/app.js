/*
 * Kiểm tra tệp đính kèm ngay trên trình duyệt (server vẫn kiểm tra lại, kể cả MIME thực).
 * Dùng cho <input type="file" data-exts="pdf,jpg" data-max-mb="10" data-max-files="5" data-have="0">
 */
(function () {
  function check(input) {
    var exts = (input.dataset.exts || '').split(',').filter(Boolean);
    var maxMb = parseFloat(input.dataset.maxMb || '0');
    var left = parseInt(input.dataset.maxFiles || '0', 10) - parseInt(input.dataset.have || '0', 10);
    var errors = [];
    var files = Array.prototype.slice.call(input.files || []);
    if (input.dataset.maxFiles && files.length > left) {
      errors.push('Mỗi phản ánh tối đa ' + input.dataset.maxFiles + ' tệp (còn được thêm ' + Math.max(0, left) + ').');
    }
    files.forEach(function (f) {
      var ext = (f.name.split('.').pop() || '').toLowerCase();
      if (exts.length && exts.indexOf(ext) < 0) errors.push('"' + f.name + '": Hệ thống chỉ nhận ' + exts.join(', ').toUpperCase() + '.');
      else if (f.size === 0) errors.push('"' + f.name + '": Tệp rỗng.');
      // 10 MB = 10.000.000 byte
      else if (maxMb && f.size > maxMb * 1000000) errors.push('"' + f.name + '": Mỗi tệp tối đa ' + maxMb + ' MB, em vui lòng chọn tệp nhỏ hơn.');
    });
    var box = input.form && input.form.querySelector('[data-file-errors]');
    if (box) {
      box.textContent = errors.join(' ');
      box.hidden = errors.length === 0;
    }
    if (errors.length) {
      input.value = '';
      if (!box) alert(errors.join('\n'));
    }
  }
  document.addEventListener('change', function (e) {
    var el = e.target;
    if (el && el.matches && el.matches('input[type=file][data-exts]')) check(el);
  });
})();
